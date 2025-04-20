<?php
class Controller {
    private $db;

    function __construct($conn) {
        $this->db = $conn;
    }

    /**
     * บันทึกข้อผิดพลาดไปยังไฟล์ log
     * 
     * @param string $message ข้อความที่ต้องการบันทึก
     * @param mixed $data ข้อมูลเพิ่มเติม (ถ้ามี)
     */
    private function logError($message, $data = null) {
        if ($data) {
            if (is_array($data) || is_object($data)) {
                $dataStr = json_encode($data);
            } else {
                $dataStr = (string)$data;
            }
            error_log($message . ": " . $dataStr);
        } else {
            error_log($message);
        }
    }

    /**
     * ดำเนินการ query ทั่วไป
     * 
     * @param string $sql คำสั่ง SQL
     * @param array $params พารามิเตอร์ (ถ้ามี)
     * @return PDOStatement|false ผลลัพธ์หรือ false ถ้าเกิดข้อผิดพลาด
     */
    private function executeQuery($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
            }
            $stmt->execute();
            return $stmt;
        } catch(PDOException $e) {
            $this->logError("Query execution failed", $e->getMessage());
            return false;
        }
    }

    /**
     * ดึงข้อมูลทั้งหมดจากตาราง
     * 
     * @param string $table ชื่อตาราง
     * @param string $orderBy ฟิลด์ที่ใช้เรียงลำดับ
     * @param string $orderDirection ทิศทางการเรียงลำดับ (ASC/DESC)
     * @return PDOStatement|false ผลลัพธ์หรือ false ถ้าเกิดข้อผิดพลาด
     */
    public function getAll($table, $orderBy = null, $orderDirection = 'ASC') {
        $sql = "SELECT * FROM $table";
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy $orderDirection";
        }
        return $this->executeQuery($sql);
    }

    /**
     * ดึงข้อมูลจากตารางตาม ID
     * 
     * @param string $table ชื่อตาราง
     * @param string $idField ชื่อฟิลด์ ID
     * @param mixed $id ค่า ID
     * @return array|false ข้อมูลหรือ false ถ้าเกิดข้อผิดพลาด
     */
    public function getById($table, $idField, $id) {
        $sql = "SELECT * FROM $table WHERE $idField = :id";
        $stmt = $this->executeQuery($sql, [':id' => $id]);
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    /**
     * ตรวจสอบการมีอยู่ของข้อมูลในตาราง
     * 
     * @param string $table ชื่อตาราง
     * @param string $idField ชื่อฟิลด์ ID
     * @param mixed $id ค่า ID
     * @return bool true ถ้ามีข้อมูล, false ถ้าไม่มี
     */
    private function recordExists($table, $idField, $id) {
        $sql = "SELECT COUNT(*) FROM $table WHERE $idField = :id";
        $stmt = $this->executeQuery($sql, [':id' => $id]);
        if ($stmt) {
            return $stmt->fetchColumn() > 0;
        }
        return false;
    }

    /**
     * เพิ่มข้อมูลลงในตาราง
     * 
     * @param string $table ชื่อตาราง
     * @param array $data ข้อมูลที่ต้องการเพิ่ม [field => value]
     * @return bool ผลการทำงาน
     */
    public function insert($table, $data) {
        try {
            $fields = array_keys($data);
            $placeholders = array_map(function($field) {
                return ":$field";
            }, $fields);
            
            $sql = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $this->db->prepare($sql);
            foreach ($data as $field => $value) {
                $stmt->bindValue(":$field", $value);
            }
            
            return $stmt->execute();
        } catch(PDOException $e) {
            $this->logError("Insert failed for table $table", $e->getMessage());
            return false;
        }
    }

    /**
     * อัปเดตข้อมูลในตาราง
     * 
     * @param string $table ชื่อตาราง
     * @param array $data ข้อมูลที่ต้องการอัปเดต [field => value]
     * @param string $idField ชื่อฟิลด์ ID
     * @param mixed $id ค่า ID
     * @return bool ผลการทำงาน
     */
    public function update($table, $data, $idField, $id) {
        try {
            // ตรวจสอบว่ามีข้อมูลอยู่จริงหรือไม่
            if (!$this->recordExists($table, $idField, $id)) {
                return false;
            }
            
            $setFields = array_map(function($field) {
                return "$field = :$field";
            }, array_keys($data));
            
            $sql = "UPDATE $table SET " . implode(', ', $setFields) . " WHERE $idField = :id";
            
            $stmt = $this->db->prepare($sql);
            foreach ($data as $field => $value) {
                $stmt->bindValue(":$field", $value);
            }
            $stmt->bindValue(":id", $id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            $this->logError("Update failed for table $table", $e->getMessage());
            return false;
        }
    }

    /**
     * ลบข้อมูลจากตาราง
     * 
     * @param string $table ชื่อตาราง
     * @param string $idField ชื่อฟิลด์ ID
     * @param mixed $id ค่า ID
     * @return bool ผลการทำงาน
     */
    public function delete($table, $idField, $id) {
        try {
            // ตรวจสอบว่ามีข้อมูลอยู่จริงหรือไม่
            if (!$this->recordExists($table, $idField, $id)) {
                return false;
            }
            
            $sql = "DELETE FROM $table WHERE $idField = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":id", $id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            $this->logError("Delete failed for table $table", $e->getMessage());
            return false;
        }
    }

    // ---- FACULTY METHODS ----
    
    // ดึงข้อมูลคณะทั้งหมด
    function getFaculties() {
        return $this->getAll('faculty', 'Fac_id', 'ASC');
    }
    
    // ดึงข้อมูลคณะตาม ID
    function getFacultyById($facId) {
        return $this->getById('faculty', 'Fac_id', $facId);
    }
    
    // เพิ่มข้อมูลคณะ
    function insertFaculty($facId, $facName) {
        return $this->insert('faculty', [
            'Fac_id' => $facId,
            'Fac_name' => $facName
        ]);
    }
    
    // อัปเดตข้อมูลคณะ
    function updateFaculty($facId, $facName) {
        return $this->update('faculty', ['Fac_name' => $facName], 'Fac_id', $facId);
    }
    
    // ลบข้อมูลคณะ
    function deleteFaculty($facId) {
        return $this->delete('faculty', 'Fac_id', $facId);
    }

    // ---- MAJOR METHODS ----

    // ดึงข้อมูลสาขาวิชาทั้งหมด
    function getMajors() {
        $sql = "SELECT m.*, f.Fac_name 
                FROM major m
                JOIN faculty f ON m.Fac_id = f.Fac_id
                ORDER BY m.Maj_id ASC";
        return $this->executeQuery($sql);
    }

    // ดึงข้อมูลสาขาวิชาตาม ID
    function getMajorById($majId) {
        return $this->getById('major', 'Maj_id', $majId);
    }

    // เพิ่มข้อมูลสาขาวิชา
    function insertMajor($majId, $majName, $facId) {
        return $this->insert('major', [
            'Maj_id' => $majId,
            'Maj_name' => $majName,
            'Fac_id' => $facId
        ]);
    }

    // อัปเดตข้อมูลสาขาวิชา
    function updateMajor($majId, $majName, $facId) {
        return $this->update('major', [
            'Maj_name' => $majName,
            'Fac_id' => $facId
        ], 'Maj_id', $majId);
    }

    // ลบข้อมูลสาขาวิชา
    function deleteMajor($majId) {
        return $this->delete('major', 'Maj_id', $majId);
    }

    // ---- ACTIVITY TYPE METHODS ----

    // ดึงข้อมูลประเภทกิจกรรมทั้งหมด
    function getActivityTypes() {
        return $this->getAll('activity_type', 'ActType_id', 'ASC');
    }

    // ดึงข้อมูลประเภทกิจกรรมตาม ID
    function getActivityTypeById($id) {
        return $this->getById('activity_type', 'ActType_id', $id);
    }

    // เพิ่มข้อมูลประเภทกิจกรรม
    function insertActivityType($id, $name) {
        return $this->insert('activity_type', [
            'ActType_id' => $id,
            'ActType_Name' => $name
        ]);
    }

    // อัปเดตข้อมูลประเภทกิจกรรม
    function updateActivityType($id, $name) {
        return $this->update('activity_type', [
            'ActType_Name' => $name
        ], 'ActType_id', $id);
    }

    // ลบข้อมูลประเภทกิจกรรม
    function deleteActivityType($id) {
        return $this->delete('activity_type', 'ActType_id', $id);
    }

    // ---- ACTIVITY METHODS ----

    // ดึงข้อมูลกิจกรรมทั้งหมด
    function getActivities() {
        $sql = "SELECT a.*, at.ActType_Name, m.Maj_name 
                FROM activity a 
                LEFT JOIN activity_type at ON a.ActType_id = at.ActType_id 
                LEFT JOIN major m ON a.Maj_id = m.Maj_id 
                ORDER BY a.Act_id DESC";
        return $this->executeQuery($sql);
    }

    // ดึงข้อมูลกิจกรรมตาม ID
    function getActivityById($actId) {
        $sql = "SELECT a.*, at.ActType_Name, m.Maj_name 
                FROM activity a 
                LEFT JOIN activity_type at ON a.ActType_id = at.ActType_id 
                LEFT JOIN major m ON a.Maj_id = m.Maj_id 
                WHERE a.Act_id = :act_id";
        $stmt = $this->executeQuery($sql, [':act_id' => $actId]);
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    // เพิ่มข้อมูลกิจกรรม
    function insertActivity($act_name, $act_hour, $act_start_date, $act_stop_date, $act_semester, $act_status, $act_year, $act_type_id, $maj_id) {
        try {
            // ค้นหารหัสกิจกรรมล่าสุด
            $query = "SELECT MAX(CAST(Act_id AS UNSIGNED)) as max_id FROM activity";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // กำหนดรหัสใหม่ (เริ่มที่ 00001 หรือเพิ่มจากรหัสล่าสุด)
            $next_id = 1;
            if ($result && $result['max_id']) {
                $next_id = intval($result['max_id']) + 1;
            }
            $act_id = str_pad($next_id, 5, '0', STR_PAD_LEFT); // รูปแบบ 00001
            
            // บันทึกข้อมูล
            return $this->insert('activity', [
                'Act_id' => $act_id,
                'Act_name' => $act_name,
                'Act_hour' => $act_hour,
                'Act_start_date' => $act_start_date,
                'Act_stop_date' => $act_stop_date,
                'ActSemester' => $act_semester,
                'ActStatus' => $act_status,
                'ActYear' => $act_year,
                'Maj_id' => $maj_id,
                'ActType_id' => $act_type_id
            ]);
        } catch(PDOException $e) {
            $this->logError("PDOException in insertActivity", $e->getMessage());
            return false;
        }
    }

    // อัปเดตข้อมูลกิจกรรม
    function updateActivity($act_id, $act_name, $act_hour, $act_start_date, $act_stop_date, $act_semester, $act_status, $act_year, $act_type_id, $maj_id) {
        return $this->update('activity', [
            'Act_name' => $act_name,
            'Act_hour' => $act_hour,
            'Act_start_date' => $act_start_date,
            'Act_stop_date' => $act_stop_date,
            'ActSemester' => $act_semester,
            'ActStatus' => $act_status,
            'ActYear' => $act_year,
            'Maj_id' => $maj_id,
            'ActType_id' => $act_type_id
        ], 'Act_id', $act_id);
    }

    // ลบข้อมูลกิจกรรม
    function deleteActivity($act_id) {
        return $this->delete('activity', 'Act_id', $act_id);
    }

    // ดึงนักศึกษาที่เข้าร่วมกิจกรรม
    function getActivityParticipants($act_id) {
        $sql = "SELECT p.*, s.Stu_fname, s.Stu_lname, m.Maj_name 
                FROM participation p 
                JOIN student s ON p.Stu_id = s.Stu_id 
                JOIN major m ON s.Maj_id = m.Maj_id 
                WHERE p.Act_id = :act_id";
        return $this->executeQuery($sql, [':act_id' => $act_id]);
    }

    // ---- ADVISOR METHODS ----

    function getProfessors() {
        $sql = "SELECT p.*, t.Title_name, m.Maj_name 
                FROM professor p 
                LEFT JOIN title t ON p.Title_id = t.Title_id 
                LEFT JOIN major m ON p.Major_id = m.Maj_id 
                ORDER BY p.Prof_id ASC";
        return $this->executeQuery($sql);
    }
    
    /**
     * ดึงข้อมูลอาจารย์ที่ปรึกษาตาม ID
     * @param int $profId รหัสอาจารย์
     * @return array|false
     */
    function getProfessorById($profId) {
        $sql = "SELECT p.*, t.Title_name, m.Maj_name 
                FROM professor p 
                LEFT JOIN title t ON p.Title_id = t.Title_id 
                LEFT JOIN major m ON p.Major_id = m.Maj_id 
                WHERE p.Prof_id = :prof_id";
        $stmt = $this->executeQuery($sql, [':prof_id' => $profId]);
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
    
    // เพิ่มข้อมูลอาจารย์ที่ปรึกษา
    function insertProfessor($prof_id, $prof_fname, $prof_lname, $phone, $email, $title_id, $major_id) {
        try {
            $prof_id = trim($prof_id);
            $prof_fname = trim($prof_fname);
            $prof_lname = trim($prof_lname);
            $phone = $phone ? trim($phone) : null;
            $email = $email ? trim($email) : null;
            $title_id = trim($title_id);
            $major_id = trim($major_id);
            
            return $this->insert('professor', [
                'Prof_id' => $prof_id,
                'Prof_fname' => $prof_fname,
                'Prof_lname' => $prof_lname,
                'Phone' => $phone,
                'Email' => $email,
                'Title_id' => $title_id,
                'Major_id' => $major_id,
                'is_status' => '2' // สถานะปกติ
            ]);
        } catch(PDOException $e) {
            $this->logError("PDOException in insertProfessor", $e->getMessage());
            return false;
        }
    }
    
    // อัปเดตข้อมูลอาจารย์ที่ปรึกษา
    function updateProfessor($prof_id, $prof_fname, $prof_lname, $phone, $email, $title_id, $major_id) {
        try {
            $prof_id = trim($prof_id);
            $prof_fname = trim($prof_fname);
            $prof_lname = trim($prof_lname);
            $phone = $phone ? trim($phone) : null;
            $email = $email ? trim($email) : null;
            $title_id = trim($title_id);
            $major_id = trim($major_id);
            
            return $this->update('professor', [
                'Prof_fname' => $prof_fname,
                'Prof_lname' => $prof_lname,
                'Phone' => $phone,
                'Email' => $email,
                'Title_id' => $title_id,
                'Major_id' => $major_id
            ], 'Prof_id', $prof_id);
        } catch(PDOException $e) {
            $this->logError("PDOException in updateProfessor", $e->getMessage());
            return false;
        }
    }
    
    // ลบข้อมูลอาจารย์ที่ปรึกษา
    function deleteProfessor($prof_id) {
        try {
            // ตรวจสอบก่อนลบว่ามีนักศึกษาในที่ปรึกษาหรือไม่
            $checkSql = "SELECT COUNT(*) FROM student WHERE Prof_id = :prof_id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindParam(':prof_id', $prof_id);
            $checkStmt->execute();
            
            $count = $checkStmt->fetchColumn();
            
            if ($count > 0) {
                // มีนักศึกษาในที่ปรึกษา ควรแจ้งเตือนก่อนลบ
                $this->logError("Cannot delete professor ID: $prof_id, has $count students");
                $_SESSION['error'] = "ไม่สามารถลบข้อมูลได้ เนื่องจากมีนักศึกษาในที่ปรึกษา $count คน";
                return false;
            }
            
            return $this->delete('professor', 'Prof_id', $prof_id);
        } catch(PDOException $e) {
            $this->logError("PDOException in deleteProfessor", $e->getMessage());
            return false;
        }
    }
    
    // ดึงข้อมูลคำนำหน้าจาก ID
    public function getTitleById($title_id) {
        return $this->getById('title', 'Title_id', $title_id);
    }
    
    // ดึงข้อมูลคำนำหน้าทั้งหมด
    function getTitles() {
        return $this->getAll('title', 'Title_id', 'ASC');
    }
    
    // ดึงนักศึกษาที่อยู่ภายใต้การดูแลของอาจารย์ที่ปรึกษา
    function getAdvisoryStudents($profId) {
        $sql = "SELECT s.*, t.Title_name, m.Maj_name, p.Plan_name
                FROM student s
                LEFT JOIN title t ON s.Title_id = t.Title_id
                LEFT JOIN major m ON s.Maj_id = m.Maj_id
                LEFT JOIN student_plan p ON s.Plan_id = p.Plan_id
                WHERE s.Prof_id = :prof_id
                ORDER BY s.Stu_id ASC";
        return $this->executeQuery($sql, [':prof_id' => $profId]);
    }

    // ---- STUDENT METHODS ----

    function getStudents() {
        $sql = "SELECT s.*, t.Title_name, m.Maj_name, p.Plan_name, 
                pr.Prof_fname, pr.Prof_lname, pt.Title_name as Prof_title
                FROM student s
                LEFT JOIN title t ON s.Title_id = t.Title_id
                LEFT JOIN major m ON s.Maj_id = m.Maj_id
                LEFT JOIN student_plan p ON s.Plan_id = p.Plan_id
                LEFT JOIN professor pr ON s.Prof_id = pr.Prof_id
                LEFT JOIN title pt ON pr.Title_id = pt.Title_id
                ORDER BY s.Stu_id ASC";
        return $this->executeQuery($sql);
    }

    function getStudentById($stu_id){
        $sql = "SELECT s.*, t.Title_name, m.Maj_name, f.Fac_name, sp.Plan_name  
                FROM student s
                LEFT JOIN title t ON s.Title_id = t.Title_id
                LEFT JOIN major m ON s.Maj_id = m.Maj_id
                LEFT JOIN faculty f ON m.Fac_id = f.Fac_id
                LEFT JOIN student_plan sp ON s.Plan_id = sp.Plan_id
                WHERE s.Stu_id = :stu_id";
        $stmt = $this->executeQuery($sql, [':stu_id' => $stu_id]);
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    // ดึงข้อมูลแผนการเรียน
    function getStudentPlans() {
        return $this->getAll('student_plan', 'Plan_id', 'ASC');
    }

    // ดึงกิจกรรมที่นักศึกษาเข้าร่วม
    function getStudentActivities($stuId) {
        $sql = "SELECT p.*, a.Act_name, a.Act_start_date, a.Act_stop_date, a.Act_hour, 
                at.ActType_Name, at.ActType_id
                FROM participation p
                JOIN activity a ON p.Act_id = a.Act_id
                LEFT JOIN activity_type at ON a.ActType_id = at.ActType_id
                WHERE p.Stu_id = :stu_id
                ORDER BY a.Act_start_date DESC";
        return $this->executeQuery($sql, [':stu_id' => $stuId]);
    }

    // เพิ่มข้อมูลนักศึกษา
    function insertStudent($stuId, $stuFname, $stuLname, $stuPhone, $stuEmail = null, $birthdate = null, $religion = null, $nationality = 'ไทย', $planId, $titleId, $profId, $majId, $curriId = null, $is_status = '2') {
        try {
            // ตรวจสอบว่ามีนักศึกษานี้ในระบบแล้วหรือไม่
            if ($this->recordExists('student', 'Stu_id', $stuId)) {
                return false;
            }
            
            // ตรวจสอบว่ามี Curri_id หรือไม่ (จำเป็นตามโครงสร้างฐานข้อมูล)
            if (!$curriId) {
                // ถ้าไม่มี ให้ดึงหลักสูตรแรกของสาขามาใช้
                $sql = "SELECT Curri_id FROM curriculum WHERE Maj_id = :maj_id LIMIT 1";
                $stmt = $this->executeQuery($sql, [':maj_id' => $majId]);
                if ($stmt && $stmt->rowCount() > 0) {
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $curriId = $result['Curri_id'];
                } else {
                    // ถ้ายังไม่มีหลักสูตรสำหรับสาขานี้ ให้ใช้หลักสูตรแรกในระบบ
                    $sql = "SELECT Curri_id FROM curriculum LIMIT 1";
                    $stmt = $this->executeQuery($sql);
                    if ($stmt && $stmt->rowCount() > 0) {
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        $curriId = $result['Curri_id'];
                    } else {
                        $this->logError("No curriculum found for student insertion");
                        return false;
                    }
                }
            }
            
            // เตรียมข้อมูล
            $stuFname = trim($stuFname);
            $stuLname = trim($stuLname);
            $stuPhone = trim($stuPhone);
            $stuEmail = $stuEmail ? trim($stuEmail) : null;
            
            return $this->insert('student', [
                'Stu_id' => $stuId,
                'Stu_fname' => $stuFname,
                'Stu_lname' => $stuLname,
                'Stu_phone' => $stuPhone,
                'Stu_email' => $stuEmail,
                'Birthdate' => $birthdate,
                'Religion' => $religion,
                'Nationality' => $nationality,
                'Plan_id' => $planId,
                'Title_id' => $titleId,
                'Prof_id' => $profId,
                'Maj_id' => $majId,
                'Curri_id' => $curriId,
                'is_status' => $is_status
            ]);
        } catch(PDOException $e) {
            $this->logError("PDOException in insertStudent", $e->getMessage());
            return false;
        }
    }

    // อัปเดตข้อมูลนักศึกษา
    function updateStudent($stuId, $stuFname, $stuLname, $stuPhone, $stuEmail = null, $birthdate = null, $religion = null, $nationality = 'ไทย', $planId, $titleId, $profId, $majId, $curriId = null, $is_status = null) {
        try {
            // ตรวจสอบว่ามีนักศึกษานี้ในระบบหรือไม่
            if (!$this->recordExists('student', 'Stu_id', $stuId)) {
                return false;
            }
            
            // เตรียมข้อมูล
            $stuFname = trim($stuFname);
            $stuLname = trim($stuLname);
            $stuPhone = trim($stuPhone);
            $stuEmail = $stuEmail ? trim($stuEmail) : null;
            
            // ตรวจสอบว่ามี Curri_id และ is_status หรือไม่
            if (!$curriId || !$is_status) {
                // ถ้าไม่มี ให้ดึงข้อมูลเดิมมา
                $sql = "SELECT Curri_id, is_status FROM student WHERE Stu_id = :stu_id";
                $stmt = $this->executeQuery($sql, [':stu_id' => $stuId]);
                if ($stmt && $stmt->rowCount() > 0) {
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $curriId = $curriId ?: $result['Curri_id'];
                    $is_status = $is_status ?: $result['is_status'];
                }
            }
            
            return $this->update('student', [
                'Stu_fname' => $stuFname,
                'Stu_lname' => $stuLname,
                'Stu_phone' => $stuPhone,
                'Stu_email' => $stuEmail,
                'Birthdate' => $birthdate,
                'Religion' => $religion,
                'Nationality' => $nationality,
                'Plan_id' => $planId,
                'Title_id' => $titleId,
                'Prof_id' => $profId,
                'Maj_id' => $majId,
                'Curri_id' => $curriId,
                'is_status' => $is_status
            ], 'Stu_id', $stuId);
        } catch(PDOException $e) {
            $this->logError("PDOException in updateStudent", $e->getMessage());
            return false;
        }
    }

    // ลบข้อมูลนักศึกษา
    function deleteStudent($stuId) {
        try {
            // ตรวจสอบว่ามีนักศึกษานี้ในระบบหรือไม่
            if (!$this->recordExists('student', 'Stu_id', $stuId)) {
                return false;
            }
            
            // เริ่ม transaction
            $this->db->beginTransaction();
            
            // ลบข้อมูลการเข้าร่วมกิจกรรมของนักศึกษา (ถ้ามี)
            $this->executeQuery("DELETE FROM participation WHERE Stu_id = :stu_id", [':stu_id' => $stuId]);
            
            // ลบข้อมูลการเทียบโอนกิจกรรม (ถ้ามี)
            $this->executeQuery("DELETE FROM comparision WHERE Stu_id = :stu_id", [':stu_id' => $stuId]);
            
            // ลบข้อมูลการเข้าสู่ระบบ (ถ้ามี)
            $this->executeQuery("DELETE FROM login WHERE Stu_id = :stu_id", [':stu_id' => $stuId]);
            
            // ลบข้อมูลนักศึกษา
            $result = $this->delete('student', 'Stu_id', $stuId);
            
            if ($result) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                $this->logError("Error in deleteStudent");
                return false;
            }
        } catch(PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->logError("PDOException in deleteStudent", $e->getMessage());
            return false;
        }
    }
    // ดึงข้อมูลหลักสูตรตามรหัสหลักสูตร
    function getCurriculumById($curriId) {
        return $this->getById('curriculum', 'Curri_id', $curriId);
    }
    
    // ดึงข้อมูลหลักสูตรตามรหัสสาขา
    function getCurriculumByMajorId($majId) {
        $sql = "SELECT * FROM curriculum WHERE Maj_id = :maj_id ORDER BY Curri_start DESC";
        return $this->executeQuery($sql, [':maj_id' => $majId]);
    }
    
    // เปลี่ยนสถานะนักศึกษา
    function updateStudentStatus($stuId, $status) {
        if (!in_array($status, ['1', '2', '3', '4'])) {
            $this->logError("Invalid student status: $status");
            return false;
        }
        
        return $this->update('student', ['is_status' => $status], 'Stu_id', $stuId);
    }
    
    // ดึงข้อมูลนักศึกษาตามสถานะ
    function getStudentsByStatus($status) {
        $sql = "SELECT s.*, t.Title_name, m.Maj_name, p.Plan_name, 
                pr.Prof_fname, pr.Prof_lname, pt.Title_name as Prof_title
                FROM student s
                LEFT JOIN title t ON s.Title_id = t.Title_id
                LEFT JOIN major m ON s.Maj_id = m.Maj_id
                LEFT JOIN student_plan p ON s.Plan_id = p.Plan_id
                LEFT JOIN professor pr ON s.Prof_id = pr.Prof_id
                LEFT JOIN title pt ON pr.Title_id = pt.Title_id
                WHERE s.is_status = :status
                ORDER BY s.Stu_id ASC";
        return $this->executeQuery($sql, [':status' => $status]);
    }
    
    // นำเข้าข้อมูลนักศึกษาจากไฟล์ Excel
    /**
 * นำเข้าข้อมูลนักศึกษาจากไฟล์ Excel
 * 
 * @param array $file ข้อมูลไฟล์ที่อัปโหลด ($_FILES['import_file'])
 * @param string $importMode โหมดการนำเข้า ('add', 'update', 'both')
 * @return array ผลลัพธ์การนำเข้า
 */
/**
 * นำเข้าข้อมูลนักศึกษาจากไฟล์ Excel
 * 
 * @param array $file ข้อมูลไฟล์ที่อัปโหลด ($_FILES['import_file'])
 * @param string $importMode โหมดการนำเข้า ('add', 'update', 'both')
 * @return array ผลลัพธ์การนำเข้า
 */
function importStudentsFromExcel($file, $importMode = 'both') {
    try {
        // ตรวจสอบว่ามีไฟล์หรือไม่
        if (!isset($file) || $file['error'] !== 0) {
            return [
                'status' => false,
                'message' => 'ไม่พบไฟล์ที่อัปโหลด หรือเกิดข้อผิดพลาดในการอัปโหลด'
            ];
        }
        
        // ตรวจสอบนามสกุลไฟล์
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['xlsx', 'xls'])) {
            return [
                'status' => false,
                'message' => 'รองรับเฉพาะไฟล์ Excel (.xlsx, .xls) เท่านั้น'
            ];
        }
        
        // บันทึกไฟล์ชั่วคราว
        $tmpFile = $file['tmp_name'];
        
        // ใช้ PhpSpreadsheet เพื่ออ่านไฟล์ Excel
        require_once __DIR__ . '/../officer/includes/vendor/autoload.php';
        
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($tmpFile);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($tmpFile);
        $worksheet = $spreadsheet->getActiveSheet();
        
        // อ่านข้อมูลทั้งหมดเป็น array
        $allData = $worksheet->toArray(null, true, true, true);
        
        // เริ่ม transaction
        $this->db->beginTransaction();
        
        $inserted = 0;
        $updated = 0;
        $errors = 0;
        $error_details = [];
        
        // กำหนดแถวแรกเป็นหัวตาราง
        $headers = array_map('trim', $allData[1]);
        
        // ตรวจสอบว่ามีคอลัมน์ที่จำเป็นหรือไม่
        $requiredHeaders = ['รหัสนักศึกษา', 'คำนำหน้า', 'ชื่อ', 'นามสกุล', 'เบอร์โทรศัพท์', 'สาขาวิชา', 'อาจารย์ที่ปรึกษา', 'แผนการเรียน'];
        
        // รองรับชื่อคอลัมน์หลายแบบ
        $headerAliases = [
            'รหัสนักศึกษา' => ['รหัสนักศึกษา', 'student_id', 'stu_id'],
            'คำนำหน้า' => ['คำนำหน้า', 'title_id', 'คำนำหน้าชื่อ'],
            'ชื่อ' => ['ชื่อ', 'student_fname', 'stu_fname', 'fname'],
            'นามสกุล' => ['นามสกุล', 'student_lname', 'stu_lname', 'lname'],
            'เบอร์โทรศัพท์' => ['เบอร์โทรศัพท์', 'student_phone', 'stu_phone', 'phone'],
            'สาขาวิชา' => ['สาขาวิชา', 'สาขา', 'maj_id', 'major_id'],
            'อาจารย์ที่ปรึกษา' => ['อาจารย์ที่ปรึกษา', 'prof_id', 'อาจารย์'],
            'แผนการเรียน' => ['แผนการเรียน', 'plan_id', 'แผนการศึกษา'],
        ];
        
        // ตรวจสอบคอลัมน์ที่จำเป็น
        $missingHeaders = [];
        foreach ($requiredHeaders as $requiredHeader) {
            $found = false;
            foreach ($headerAliases[$requiredHeader] as $alias) {
                if (in_array($alias, $headers)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missingHeaders[] = $requiredHeader;
            }
        }
        
        if (!empty($missingHeaders)) {
            return [
                'status' => false,
                'message' => 'ไฟล์ Excel ไม่มีคอลัมน์ที่จำเป็น: ' . implode(', ', $missingHeaders)
            ];
        }
        
        // หาตำแหน่งคอลัมน์จากหัวตาราง
        $colIndexes = [];
        foreach ($headers as $col => $header) {
            $headerLower = strtolower(trim($header));
            
            foreach ($headerAliases as $key => $aliases) {
                foreach ($aliases as $alias) {
                    if (strtolower($alias) === $headerLower) {
                        $colIndexMap = [
                            'รหัสนักศึกษา' => 'student_id',
                            'คำนำหน้า' => 'title_id',
                            'ชื่อ' => 'student_fname',
                            'นามสกุล' => 'student_lname',
                            'เบอร์โทรศัพท์' => 'student_phone',
                            'สาขาวิชา' => 'major_id',
                            'อาจารย์ที่ปรึกษา' => 'prof_id',
                            'แผนการเรียน' => 'plan_id',
                        ];
                        $colIndexes[$colIndexMap[$key]] = $col;
                        break 2;
                    }
                }
            }
            
            // คอลัมน์อื่นๆ ที่ไม่จำเป็น
            if ($headerLower === 'อีเมล' || $headerLower === 'student_email' || $headerLower === 'email') {
                $colIndexes['student_email'] = $col;
            } else if ($headerLower === 'วันเกิด' || $headerLower === 'birthdate') {
                $colIndexes['birthdate'] = $col;
            } else if ($headerLower === 'ศาสนา' || $headerLower === 'religion') {
                $colIndexes['religion'] = $col;
            } else if ($headerLower === 'สัญชาติ' || $headerLower === 'nationality') {
                $colIndexes['nationality'] = $col;
            } else if ($headerLower === 'หลักสูตร' || $headerLower === 'curriculum_id') {
                $colIndexes['curriculum_id'] = $col;
            } else if ($headerLower === 'สถานะ' || $headerLower === 'is_status') {
                $colIndexes['is_status'] = $col;
            }
        }
        
        // เริ่มอ่านข้อมูลจากแถวที่ 2 (ข้ามหัวตาราง)
        for ($row = 2; $row <= count($allData); $row++) {
            try {
                $rowData = $allData[$row];
                
                // ข้ามแถวว่าง
                if (empty($rowData)) {
                    continue;
                }
                
                // อ่านข้อมูลจากแต่ละคอลัมน์
                $stuId = isset($colIndexes['student_id']) && isset($rowData[$colIndexes['student_id']]) ? trim($rowData[$colIndexes['student_id']]) : '';
                $titleName = isset($colIndexes['title_id']) && isset($rowData[$colIndexes['title_id']]) ? trim($rowData[$colIndexes['title_id']]) : '';
                $stuFname = isset($colIndexes['student_fname']) && isset($rowData[$colIndexes['student_fname']]) ? trim($rowData[$colIndexes['student_fname']]) : '';
                $stuLname = isset($colIndexes['student_lname']) && isset($rowData[$colIndexes['student_lname']]) ? trim($rowData[$colIndexes['student_lname']]) : '';
                $stuPhone = isset($colIndexes['student_phone']) && isset($rowData[$colIndexes['student_phone']]) ? trim($rowData[$colIndexes['student_phone']]) : '';
                $stuEmail = isset($colIndexes['student_email']) && isset($rowData[$colIndexes['student_email']]) ? trim($rowData[$colIndexes['student_email']]) : '';
                $birthdateVal = isset($colIndexes['birthdate']) && isset($rowData[$colIndexes['birthdate']]) ? $rowData[$colIndexes['birthdate']] : '';
                $religion = isset($colIndexes['religion']) && isset($rowData[$colIndexes['religion']]) ? trim($rowData[$colIndexes['religion']]) : '';
                $nationality = isset($colIndexes['nationality']) && isset($rowData[$colIndexes['nationality']]) ? trim($rowData[$colIndexes['nationality']]) : 'ไทย';
                $planName = isset($colIndexes['plan_id']) && isset($rowData[$colIndexes['plan_id']]) ? trim($rowData[$colIndexes['plan_id']]) : '';
                $majorName = isset($colIndexes['major_id']) && isset($rowData[$colIndexes['major_id']]) ? trim($rowData[$colIndexes['major_id']]) : '';
                $profName = isset($colIndexes['prof_id']) && isset($rowData[$colIndexes['prof_id']]) ? trim($rowData[$colIndexes['prof_id']]) : '';
                $curriName = isset($colIndexes['curriculum_id']) && isset($rowData[$colIndexes['curriculum_id']]) ? trim($rowData[$colIndexes['curriculum_id']]) : '';
                $isStatus = isset($colIndexes['is_status']) && isset($rowData[$colIndexes['is_status']]) ? trim($rowData[$colIndexes['is_status']]) : '2';
                
                // ข้ามแถวที่ไม่มีข้อมูลสำคัญ
                if (empty($stuId) || empty($stuFname) || empty($stuLname)) {
                    continue;
                }
                
                // แปลงรูปแบบวันเกิด
                $birthdate = null;
                if (!empty($birthdateVal)) {
                    // แปลงวันเกิดให้อยู่ในรูปแบบ Y-m-d
                    if (is_numeric($birthdateVal)) {
                        // กรณีเป็น Excel timestamp
                        $birthdate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($birthdateVal)->format('Y-m-d');
                    } else {
                        $birthdateObj = null;
                        // ลองแปลงจากหลายรูปแบบ
                        $dateFormats = ['d/m/Y', 'Y-m-d', 'm/d/Y', 'd-m-Y', 'Y/m/d'];
                        foreach ($dateFormats as $format) {
                            $dateObj = \DateTime::createFromFormat($format, $birthdateVal);
                            if ($dateObj !== false) {
                                $birthdateObj = $dateObj;
                                break;
                            }
                        }
                        $birthdate = $birthdateObj ? $birthdateObj->format('Y-m-d') : null;
                    }
                }
                
                // ตรวจสอบและค้นหา title_id จากชื่อคำนำหน้า
                $titleId = null;
                if (!empty($titleName)) {
                    if (is_numeric($titleName)) {
                        $titleId = $titleName;
                    } else {
                        $sql = "SELECT Title_id FROM title WHERE Title_name = :title_name";
                        $stmt = $this->executeQuery($sql, [':title_name' => $titleName]);
                        if ($stmt && $stmt->rowCount() > 0) {
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            $titleId = $result['Title_id'];
                        } else {
                            // ถ้าไม่พบ ใช้ค่าเริ่มต้น
                            $titleId = "1"; // นาย
                            $error_details[] = "แถวที่ " . $row . ": ไม่พบคำนำหน้า \"$titleName\" ใช้ค่าเริ่มต้นแทน";
                        }
                    }
                } else {
                    $titleId = "1"; // ค่าเริ่มต้น: นาย
                }
                
                // ตรวจสอบและค้นหา maj_id จากชื่อสาขา
                $majId = null;
                if (!empty($majorName)) {
                    if (is_numeric($majorName)) {
                        $majId = $majorName;
                    } else {
                        $sql = "SELECT Maj_id FROM major WHERE Maj_name = :maj_name";
                        $stmt = $this->executeQuery($sql, [':maj_name' => $majorName]);
                        if ($stmt && $stmt->rowCount() > 0) {
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            $majId = $result['Maj_id'];
                        } else {
                            throw new Exception("ไม่พบสาขาวิชา \"$majorName\" ในระบบ");
                        }
                    }
                } else {
                    throw new Exception("ไม่ระบุสาขาวิชา");
                }
                
                // ตรวจสอบและค้นหา plan_id จากชื่อแผนการเรียน
                $planId = null;
                if (!empty($planName)) {
                    if (is_numeric($planName)) {
                        $planId = $planName;
                    } else {
                        // ค้นหาจากชื่อเต็มหรือชื่อย่อ
                        $sql = "SELECT Plan_id FROM student_plan WHERE Plan_name = :plan_name OR Abbre = :plan_abbre";
                        $stmt = $this->executeQuery($sql, [':plan_name' => $planName, ':plan_abbre' => $planName]);
                        if ($stmt && $stmt->rowCount() > 0) {
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            $planId = $result['Plan_id'];
                        } else {
                            throw new Exception("ไม่พบแผนการเรียน \"$planName\" ในระบบ");
                        }
                    }
                } else {
                    throw new Exception("ไม่ระบุแผนการเรียน");
                }
                
                // ตรวจสอบและค้นหา prof_id จากชื่ออาจารย์
                $profId = null;
                if (!empty($profName)) {
                    if (is_numeric($profName)) {
                        $profId = $profName;
                    } else {
                        // ค้นหาจากรหัสอาจารย์
                        $sql = "SELECT Prof_id FROM professor WHERE Prof_id = :prof_id";
                        $stmt = $this->executeQuery($sql, [':prof_id' => $profName]);
                        if ($stmt && $stmt->rowCount() > 0) {
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            $profId = $result['Prof_id'];
                        } else {
                            // ค้นหาจากชื่อ-นามสกุล (ค้นแบบคร่าวๆ)
                            $nameParts = explode(' ', $profName);
                            if (count($nameParts) >= 2) {
                                $firstName = $nameParts[0];
                                $lastName = end($nameParts);
                                
                                $sql = "SELECT Prof_id FROM professor WHERE Prof_fname LIKE :fname AND Prof_lname LIKE :lname";
                                $stmt = $this->executeQuery($sql, [':fname' => "%$firstName%", ':lname' => "%$lastName%"]);
                                if ($stmt && $stmt->rowCount() > 0) {
                                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                    $profId = $result['Prof_id'];
                                } else {
                                    throw new Exception("ไม่พบอาจารย์ที่ปรึกษา \"$profName\" ในระบบ");
                                }
                            } else {
                                throw new Exception("ชื่ออาจารย์ที่ปรึกษาไม่ถูกต้อง \"$profName\"");
                            }
                        }
                    }
                } else {
                    throw new Exception("ไม่ระบุอาจารย์ที่ปรึกษา");
                }
                
                // ตรวจสอบและค้นหา curri_id
                $curriId = null;
                if (!empty($curriName)) {
                    if (is_numeric($curriName)) {
                        $curriId = $curriName;
                    } else {
                        $sql = "SELECT Curri_id FROM curriculum WHERE Curri_tname LIKE :curri_name OR Curri_t LIKE :curri_short OR Curri_ename LIKE :curri_ename OR Curri_e LIKE :curri_eshort";
                        $stmt = $this->executeQuery($sql, [
                            ':curri_name' => "%$curriName%", 
                            ':curri_short' => "%$curriName%",
                            ':curri_ename' => "%$curriName%",
                            ':curri_eshort' => "%$curriName%"
                        ]);
                        if ($stmt && $stmt->rowCount() > 0) {
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            $curriId = $result['Curri_id'];
                        } else {
                            // ถ้าไม่พบ ให้ใช้หลักสูตรแรกของสาขานั้น
                            $sql = "SELECT Curri_id FROM curriculum WHERE Maj_id = :maj_id ORDER BY Curri_start DESC LIMIT 1";
                            $stmt = $this->executeQuery($sql, [':maj_id' => $majId]);
                            if ($stmt && $stmt->rowCount() > 0) {
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                $curriId = $result['Curri_id'];
                                $error_details[] = "แถวที่ " . $row . ": ไม่พบหลักสูตร \"$curriName\" ใช้หลักสูตรล่าสุดของสาขาแทน";
                            } else {
                                throw new Exception("ไม่พบหลักสูตรสำหรับสาขา $majorName");
                            }
                        }
                    }
                } else {
                    // ถ้าไม่ระบุหลักสูตร ให้ใช้หลักสูตรล่าสุดของสาขา
                    $sql = "SELECT Curri_id FROM curriculum WHERE Maj_id = :maj_id ORDER BY Curri_start DESC LIMIT 1";
                    $stmt = $this->executeQuery($sql, [':maj_id' => $majId]);
                    if ($stmt && $stmt->rowCount() > 0) {
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        $curriId = $result['Curri_id'];
                    } else {
                        throw new Exception("ไม่พบหลักสูตรสำหรับสาขา $majorName");
                    }
                }
                
                // ตรวจสอบสถานะ
                if (!empty($isStatus)) {
                    if (!in_array($isStatus, ['1', '2', '3', '4'])) {
                        $isStatus = '2'; // ค่าเริ่มต้น: สถานะปกติ
                        $error_details[] = "แถวที่ " . $row . ": สถานะไม่ถูกต้อง ใช้ค่าเริ่มต้น (ปกติ) แทน";
                    }
                } else {
                    $isStatus = '2'; // ค่าเริ่มต้น: สถานะปกติ
                }
                
                // ตรวจสอบรูปแบบเบอร์โทรศัพท์
                if (!empty($stuPhone)) {
                    // ลบอักขระที่ไม่ใช่ตัวเลขออก
                    $stuPhone = preg_replace('/[^0-9]/', '', $stuPhone);
                    if (strlen($stuPhone) !== 10) {
                        $error_details[] = "แถวที่ " . $row . ": เบอร์โทรศัพท์ไม่ถูกต้อง (ควรมี 10 หลัก)";
                    }
                }
                
                // ตรวจสอบว่ามีนักศึกษานี้ในระบบแล้วหรือไม่
                $existingStudent = $this->getStudentById($stuId);
                
                // ตัดสินใจว่าจะเพิ่มหรืออัปเดตข้อมูล
                if ($existingStudent) {
                    // มีข้อมูลนักศึกษานี้อยู่แล้ว
                    if ($importMode == 'add') {
                        // โหมดเพิ่มข้อมูลใหม่เท่านั้น
                        $error_details[] = "แถวที่ " . $row . ": รหัสนักศึกษา $stuId มีอยู่ในระบบแล้ว (ข้ามไป)";
                        continue;
                    } else {
                        // โหมดอัปเดตหรือทั้งสองโหมด
                        $updateResult = $this->updateStudent(
                            $stuId, 
                            $stuFname, 
                            $stuLname, 
                            $stuPhone, 
                            $stuEmail, 
                            $birthdate, 
                            $religion, 
                            $nationality, 
                            $planId, 
                            $titleId, 
                            $profId, 
                            $majId, 
                            $curriId, 
                            $isStatus
                        );
                        
                        if ($updateResult) {
                            $updated++;
                        } else {
                            $errors++;
                            $error_details[] = "แถวที่ " . $row . ": เกิดข้อผิดพลาดในการอัปเดตข้อมูลนักศึกษา $stuId";
                        }
                    }
                } else {
                    // ไม่มีข้อมูลนักศึกษานี้
                    if ($importMode == 'update') {
                        // โหมดอัปเดตเท่านั้น
                        $error_details[] = "แถวที่ " . $row . ": ไม่พบรหัสนักศึกษา $stuId ในระบบ (ข้ามไป)";
                        continue;
                    } else {
                        // โหมดเพิ่มหรือทั้งสองโหมด
                        $insertResult = $this->insertStudent(
                            $stuId, 
                            $stuFname, 
                            $stuLname, 
                            $stuPhone, 
                            $stuEmail, 
                            $birthdate, 
                            $religion, 
                            $nationality, 
                            $planId, 
                            $titleId, 
                            $profId, 
                            $majId, 
                            $curriId, 
                            $isStatus
                        );
                        
                        if ($insertResult) {
                            $inserted++;
                            // สร้างบัญชีผู้ใช้สำหรับนักศึกษาใหม่
                            $this->createLoginForStudent($stuId);
                        } else {
                            $errors++;
                            $error_details[] = "แถวที่ " . $row . ": เกิดข้อผิดพลาดในการเพิ่มข้อมูลนักศึกษา $stuId";
                        }
                    }
                }
            } catch (Exception $e) {
                $errors++;
                $error_details[] = "แถวที่ " . $row . ": " . $e->getMessage();
            }
        }
        
        // ถ้าไม่มีข้อมูลที่นำเข้าสำเร็จเลย และมีข้อผิดพลาด ให้ rollback
        if ($inserted == 0 && $updated == 0 && $errors > 0) {
            $this->db->rollBack();
            return [
                'status' => false,
                'message' => 'ไม่สามารถนำเข้าข้อมูลได้ มีข้อผิดพลาด ' . $errors . ' รายการ',
                'error_details' => $error_details
            ];
        }
        
        // ทุกอย่างเรียบร้อย commit transaction
        $this->db->commit();
        
        return [
            'status' => true,
            'inserted' => $inserted,
            'updated' => $updated,
            'errors' => $errors,
            'error_details' => $error_details,
            'message' => 'นำเข้าข้อมูลสำเร็จ'
        ];
    } catch(\Exception $e) {
        // มีข้อผิดพลาดที่ไม่คาดคิด
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        
        $this->logError("importStudentsFromExcel error", $e->getMessage());
        
        return [
            'status' => false,
            'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
        ];
    }
}
    
    // ---- COMPARISION METHODS ----

    /**
     * ดึงข้อมูลการขอเทียบกิจกรรมตามรหัสนักศึกษา
     * 
     * @param string $studentId รหัสนักศึกษา
     * @return array ข้อมูลการขอเทียบกิจกรรม
     */
    public function getComparisionsByStudentId($studentId) {
        $sql = "SELECT * FROM comparision WHERE Stu_id = :student_id ORDER BY Com_id DESC";
        $stmt = $this->executeQuery($sql, [':student_id' => $studentId]);
        
        if (!$stmt) {
            return array();
        }
        
        $comparisions = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $comparisions[] = $row;
        }
        
        return $comparisions;
    }

    /**
     * ดึงข้อมูลการขอเทียบกิจกรรมตามรหัสคำร้อง
     * 
     * @param int $comId รหัสคำร้อง
     * @return array|bool ข้อมูลการขอเทียบกิจกรรมหรือ false ถ้าไม่พบ
     */
    public function getComparisionById($comId) {
        return $this->getById('comparision', 'Com_id', $comId);
    }

    /**
     * เพิ่มข้อมูลการขอเทียบกิจกรรม
     * 
     * @param int $actAmount จำนวนครั้งที่เข้าร่วม
     * @param int $actHour จำนวนชั่วโมง
     * @param string $upload ชื่อไฟล์เอกสาร (ถ้ามี)
     * @param string $actSemester ภาคเรียน (เช่น 1/2567)
     * @param string $actYear ปีการศึกษา (Date format)
     * @param string $actId รหัสกิจกรรม
     * @param string $studentId รหัสนักศึกษา
     * @return bool ผลการทำงาน
     */
    public function insertComparision($actAmount, $actHour, $upload, $actSemester, $actYear, $actId, $studentId) {
        return $this->insert('comparision', [
            'Act_amount' => $actAmount,
            'Act_hour' => $actHour,
            'Upload' => $upload,
            'ActSemester' => $actSemester,
            'ActYear' => $actYear,
            'Act_id' => $actId,
            'Stu_id' => $studentId
        ]);
    }

    /**
     * อัปเดตข้อมูลการขอเทียบกิจกรรม
     * 
     * @param int $comId รหัสคำร้อง
     * @param int $actAmount จำนวนครั้งที่เข้าร่วม
     * @param int $actHour จำนวนชั่วโมง
     * @param string $upload ชื่อไฟล์เอกสาร (ถ้ามี)
     * @param string $actSemester ภาคเรียน
     * @param string $actYear ปีการศึกษา
     * @param string $actId รหัสกิจกรรม
     * @param string $studentId รหัสนักศึกษา
     * @return bool ผลการทำงาน
     */
    public function updateComparision($comId, $actAmount, $actHour, $upload, $actSemester, $actYear, $actId, $studentId) {
        $data = [
            'Act_amount' => $actAmount,
            'Act_hour' => $actHour,
            'Upload' => $upload,
            'ActSemester' => $actSemester,
            'ActYear' => $actYear,
            'Act_id' => $actId
        ];
        
        return $this->update('comparision', $data, 'Com_id', $comId);
    }

    /**
     * ลบข้อมูลการขอเทียบกิจกรรม
     */
    public function deleteComparision($comId) {
        return $this->delete('comparision', 'Com_id', $comId);
    }

    /**
     * ดึงข้อมูลการขอเทียบกิจกรรมทั้งหมด (สำหรับผู้ดูแลระบบ)
     * 
     * @return array ข้อมูลการขอเทียบกิจกรรม
     */
    public function getAllComparisions() {
        $sql = "SELECT c.*, s.Stu_fname, s.Stu_lname, s.Maj_id, t.Title_name 
                FROM comparision c 
                LEFT JOIN student s ON c.Stu_id = s.Stu_id 
                LEFT JOIN title t ON s.Title_id = t.Title_id 
                ORDER BY c.Com_id DESC";
        $stmt = $this->executeQuery($sql);
        
        if (!$stmt) {
            return array();
        }
        
        $comparisions = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $comparisions[] = $row;
        }
        
        return $comparisions;
    }

    /**
     * ดึงข้อมูลการขอเทียบกิจกรรมที่อนุมัติแล้วของนักศึกษา
     * 
     * หมายเหตุ: ตามโครงสร้างฐานข้อมูลปัจจุบัน ไม่มีฟิลด์ Status
     * จึงต้องดัดแปลงเมธอดนี้
     * 
     * @param string $studentId รหัสนักศึกษา
     * @return array ข้อมูลการขอเทียบกิจกรรมที่อนุมัติแล้ว
     */
    public function getApprovedComparisionsByStudentId($studentId) {
        // ดึงข้อมูลทั้งหมดแทน เนื่องจากไม่มีฟิลด์ Status
        return $this->getComparisionsByStudentId($studentId);
    }

    /**
     * ค้นหาข้อมูลการขอเทียบกิจกรรมตามเงื่อนไข (สำหรับผู้ดูแลระบบ)
     * 
     * @param string $keyword คำค้นหา
     * @return array ข้อมูลการขอเทียบกิจกรรม
     */
    public function searchComparisions($keyword) {
        $sql = "SELECT c.*, s.Stu_fname, s.Stu_lname, s.Maj_id, t.Title_name 
                FROM comparision c 
                LEFT JOIN student s ON c.Stu_id = s.Stu_id 
                LEFT JOIN title t ON s.Title_id = t.Title_id 
                WHERE c.Stu_id LIKE :keyword 
                OR s.Stu_fname LIKE :keyword 
                OR s.Stu_lname LIKE :keyword 
                ORDER BY c.Com_id DESC";
        
        $keyword = "%" . $keyword . "%";
        $stmt = $this->executeQuery($sql, [':keyword' => $keyword]);
        
        if (!$stmt) {
            return array();
        }
        
        $comparisions = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $comparisions[] = $row;
        }
        
        return $comparisions;
    }

    /**
     * ดึงข้อมูลนักศึกษาพร้อมคำนำหน้าชื่อตามรหัสนักศึกษา
     * 
     * @param string $studentId รหัสนักศึกษา
     * @return array|false ข้อมูลนักศึกษาหรือ false ถ้าไม่พบ
     */
    public function getStudentWithTitleById($studentId) {
        try {
            $query = "SELECT s.*, t.Title_name 
                    FROM student s 
                    LEFT JOIN title t ON s.Title_id = t.Title_id 
                    WHERE s.Stu_id = :student_id";
            
            $stmt = $this->executeQuery($query, [':student_id' => $studentId]);
            
            if ($stmt && $stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            return false;
        } catch (PDOException $e) {
            $this->logError("Get student with title error", $e->getMessage());
            return false;
        }
    }

    // ---- USER METHODS ----

    /**
     * ดึงข้อมูลสถานะผู้ใช้จากตาราง login
     */
    public function getUserStatus($user_id) {
        $sql = "SELECT status FROM login WHERE user_id = :user_id";
        $stmt = $this->executeQuery($sql, [':user_id' => $user_id]);
        
        if ($stmt && $stmt->rowCount() > 0) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['status'];
        }
        
        return false;
    }

    /**
     * ตรวจสอบรหัสผ่านผู้ใช้
     */
    public function verifyPassword($user_id, $password) {
        $sql = "SELECT user_pass FROM login WHERE user_id = :user_id";
        $stmt = $this->executeQuery($sql, [':user_id' => $user_id]);
        
        if ($stmt && $stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stored_password = $row['user_pass'];
            
            // ลองหลายวิธีการตรวจสอบรหัสผ่าน
            if (password_verify($password, $stored_password)) {
                return true;
            } else if (md5($password) === $stored_password) {
                return true;
            } else if ($password === $stored_password) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * อัปเดตรหัสผ่านผู้ใช้
     */
    public function updatePassword($user_id, $new_password) {
        // ใช้ MD5 เพื่อให้ตรงกับระบบล็อกอิน
        $hashed_password = md5($new_password);
        
        return $this->update('login', ['user_pass' => $hashed_password], 'user_id', $user_id);
    }

    // ---- CRITERIA METHODS ----

    /**
     * ดึงข้อมูลหลักเกณฑ์ทั้งหมด
     */
    function getCriteria() {
        $sql = "SELECT c.*, cu.Curri_tname, cu.Curri_t, sp.Plan_name, at.ActType_Name 
                FROM criteria c
                LEFT JOIN curriculum cu ON c.Curri_id = cu.Curri_id
                LEFT JOIN student_plan sp ON c.Plan_id = sp.Plan_id
                LEFT JOIN activity_type at ON c.ActType_id = at.ActType_id
                ORDER BY c.Crit_id ASC";
        
        return $this->executeQuery($sql);
    }

    /**
     * ดึงข้อมูลหลักเกณฑ์ตาม ID
     */
    function getCriteriaById($critId) {
        return $this->getById('criteria', 'Crit_id', $critId);
    }

    /**
     * เพิ่มข้อมูลหลักเกณฑ์ โดยสร้างรหัสอัตโนมัติ
     */
    function insertCriteria($critName, $curriId, $planId, $actTypeId, $actHour, $actAmount = 1) {
        try {
            // ค้นหารหัสหลักเกณฑ์ล่าสุด
            $query = "SELECT MAX(CAST(SUBSTRING(Crit_id, 2) AS UNSIGNED)) as max_id FROM criteria WHERE Crit_id LIKE 'C%'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // กำหนดรหัสใหม่ (เริ่มที่ C001 หรือเพิ่มจากรหัสล่าสุด)
            $next_id = 1;
            if ($result && $result['max_id']) {
                $next_id = intval($result['max_id']) + 1;
            }
            $critId = 'C' . str_pad($next_id, 3, '0', STR_PAD_LEFT); // รูปแบบ C001
            
            // บันทึกข้อมูลหลักเกณฑ์
            return $this->insert('criteria', [
                'Crit_id' => $critId,
                'Crit_name' => $critName,
                'Curri_id' => $curriId,
                'Plan_id' => $planId,
                'ActType_id' => $actTypeId,
                'Act_hour' => $actHour,
                'Act_amount' => $actAmount
            ]);
        } catch(PDOException $e) {
            $this->logError("PDOException in insertCriteria", $e->getMessage());
            return false;
        }
    }

    /**
     * อัปเดตข้อมูลหลักเกณฑ์
     */
    function updateCriteria($critId, $critName, $curriId, $planId, $actTypeId, $actHour, $actAmount = null) {
        $data = [
            'Crit_name' => $critName,
            'Curri_id' => $curriId,
            'Plan_id' => $planId,
            'ActType_id' => $actTypeId,
            'Act_hour' => $actHour
        ];
        
        if ($actAmount !== null) {
            $data['Act_amount'] = $actAmount;
        }
        
        return $this->update('criteria', $data, 'Crit_id', $critId);
    }

    /**
     * ลบข้อมูลหลักเกณฑ์
     */
    function deleteCriteria($critId) {
        return $this->delete('criteria', 'Crit_id', $critId);
    }

    /**
     * ดึงข้อมูลหลักสูตรทั้งหมด
     */
    function getCurriculum() {
        return $this->getAll('curriculum', 'Curri_tname', 'ASC');
    }
    
    // ---- ADDITIONAL METHODS ----
    
    // ตรวจสอบว่านักศึกษาเข้าร่วมกิจกรรมหรือไม่
    public function checkStudentParticipation($stuId, $actId) {
        $sql = "SELECT COUNT(*) FROM participation WHERE Stu_id = :stu_id AND Act_id = :act_id";
        $stmt = $this->executeQuery($sql, [':stu_id' => $stuId, ':act_id' => $actId]);
        
        if ($stmt) {
            return $stmt->fetchColumn() > 0;
        }
        
        return false;
    }

    // เพิ่มข้อมูลการเข้าร่วมกิจกรรม
    public function addParticipation($stuId, $actId, $actSemester, $actYear, $actHour, $checkIn, $checkOut) {
        if ($this->checkStudentParticipation($stuId, $actId)) {
            return false; // นักศึกษาเข้าร่วมกิจกรรมนี้แล้ว
        }
        
        return $this->insert('participation', [
            'Stu_id' => $stuId,
            'Act_id' => $actId,
            'ActSemester' => $actSemester,
            'ActYear' => $actYear,
            'Act_hour' => $actHour,
            'CheckIn' => $checkIn,
            'CheckOut' => $checkOut
        ]);
    }

    // ลบข้อมูลการเข้าร่วมกิจกรรม
    public function removeParticipation($stuId, $actId) {
        $sql = "DELETE FROM participation WHERE Stu_id = :stu_id AND Act_id = :act_id";
        return $this->executeQuery($sql, [':stu_id' => $stuId, ':act_id' => $actId]) !== false;
    }

    // ดึงข้อมูลสรุปกิจกรรมของนักศึกษา
    public function getStudentActivitySummary($stuId) {
        try {
            // นับจำนวนกิจกรรมและชั่วโมงสะสม
            $sql = "SELECT 
                    COUNT(*) as total_activities,
                    SUM(Act_hour) as total_hours
                    FROM participation 
                    WHERE Stu_id = :stu_id";
            
            $stmt = $this->executeQuery($sql, [':stu_id' => $stuId]);
            
            if ($stmt) {
                $summary = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // เพิ่มข้อมูลจากตาราง comparision (ถ้ามี)
                $sqlComparision = "SELECT 
                                COUNT(*) as total_comparisions,
                                SUM(Act_hour) as total_comp_hours
                                FROM comparision 
                                WHERE Stu_id = :stu_id";
                
                $stmtComp = $this->executeQuery($sqlComparision, [':stu_id' => $stuId]);
                
                if ($stmtComp) {
                    $compSummary = $stmtComp->fetch(PDO::FETCH_ASSOC);
                    $summary['total_activities'] += $compSummary['total_comparisions'];
                    $summary['total_hours'] += $compSummary['total_comp_hours'];
                }
                
                return $summary;
            }
            
            return [
                'total_activities' => 0,
                'total_hours' => 0
            ];
        } catch(PDOException $e) {
            $this->logError("Error in getStudentActivitySummary", $e->getMessage());
            return [
                'total_activities' => 0,
                'total_hours' => 0
            ];
        }
    }

    // ดึงข้อมูลการเข้าร่วมกิจกรรมตามภาคเรียน
    public function getParticipationBySemester($stuId, $semester, $year) {
        $sql = "SELECT p.*, a.Act_name, a.Act_start_date, a.Act_stop_date, at.ActType_Name
                FROM participation p
                JOIN activity a ON p.Act_id = a.Act_id
                LEFT JOIN activity_type at ON a.ActType_id = at.ActType_id
                WHERE p.Stu_id = :stu_id
                AND p.ActSemester = :semester
                AND p.ActYear = :year
                ORDER BY a.Act_start_date DESC";
        
        $stmt = $this->executeQuery($sql, [
            ':stu_id' => $stuId,
            ':semester' => $semester,
            ':year' => $year
        ]);
        
        if (!$stmt) {
            return array();
        }
        
        $participations = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $participations[] = $row;
        }
        
        return $participations;
    }

    // ดึงข้อมูลการเข้าร่วมกิจกรรมตามประเภทกิจกรรม
    public function getParticipationByActivityType($stuId, $actTypeId) {
        $sql = "SELECT p.*, a.Act_name, a.Act_start_date, a.Act_stop_date, at.ActType_Name
                FROM participation p
                JOIN activity a ON p.Act_id = a.Act_id
                LEFT JOIN activity_type at ON a.ActType_id = at.ActType_id
                WHERE p.Stu_id = :stu_id
                AND a.ActType_id = :act_type_id
                ORDER BY a.Act_start_date DESC";
        
        $stmt = $this->executeQuery($sql, [
            ':stu_id' => $stuId,
            ':act_type_id' => $actTypeId
        ]);
        
        if (!$stmt) {
            return array();
        }
        
        $participations = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $participations[] = $row;
        }
        
        return $participations;
    }

    // สร้างรหัสการเข้าสู่ระบบสำหรับนักศึกษาใหม่
    public function createLoginForStudent($stuId, $status = 'student') {
        $username = $stuId;
        $password = md5('password'); // รหัสผ่านเริ่มต้น
        $currentDate = date('Y-m-d');
        
        // ตรวจสอบว่ามี username นี้อยู่แล้วหรือไม่
        $sql = "SELECT * FROM login WHERE user_id = :username";
        $stmt = $this->executeQuery($sql, [':username' => $username]);
        
        if ($stmt && $stmt->rowCount() > 0) {
            return false; // มี username นี้อยู่แล้ว
        }
        
        // เพิ่มข้อมูลการเข้าสู่ระบบ
        return $this->insert('login', [
            'user_id' => $username,
            'user_pass' => $password,
            'status' => $status,
            'time' => $currentDate,
            'Stu_id' => $stuId,
            'Prof_id' => null
        ]);
    }

    // สร้างรหัสการเข้าสู่ระบบสำหรับอาจารย์ใหม่
    public function createLoginForProfessor($profId, $status = 'professor') {
        $username = 'prof' . $profId;
        $password = md5('password'); // รหัสผ่านเริ่มต้น
        $currentDate = date('Y-m-d');
        
        // ตรวจสอบว่ามี username นี้อยู่แล้วหรือไม่
        $sql = "SELECT * FROM login WHERE user_id = :username";
        $stmt = $this->executeQuery($sql, [':username' => $username]);
        
        if ($stmt && $stmt->rowCount() > 0) {
            return false; // มี username นี้อยู่แล้ว
        }
        
        // เพิ่มข้อมูลการเข้าสู่ระบบ
        return $this->insert('login', [
            'user_id' => $username,
            'user_pass' => $password,
            'status' => $status,
            'time' => $currentDate,
            'Stu_id' => null,
            'Prof_id' => $profId
        ]);
    }

    // ดึงข้อมูลกิจกรรมตามสาขาวิชา
    public function getActivitiesByMajor($majId) {
        $sql = "SELECT a.*, at.ActType_Name 
                FROM activity a 
                LEFT JOIN activity_type at ON a.ActType_id = at.ActType_id 
                WHERE a.Maj_id = :maj_id 
                ORDER BY a.Act_start_date DESC";
        
        $stmt = $this->executeQuery($sql, [':maj_id' => $majId]);
        
        if (!$stmt) {
            return array();
        }
        
        $activities = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $activities[] = $row;
        }
        
        return $activities;
    }

    // ดึงข้อมูลกิจกรรมตามภาคเรียน
    public function getActivitiesBySemester($semester, $year) {
        $sql = "SELECT a.*, at.ActType_Name, m.Maj_name 
                FROM activity a 
                LEFT JOIN activity_type at ON a.ActType_id = at.ActType_id 
                LEFT JOIN major m ON a.Maj_id = m.Maj_id 
                WHERE a.ActSemester = :semester 
                AND a.ActYear = :year
                ORDER BY a.Act_start_date DESC";
        
        $stmt = $this->executeQuery($sql, [
            ':semester' => $semester,
            ':year' => $year
        ]);
        
        if (!$stmt) {
            return array();
        }
        
        $activities = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $activities[] = $row;
        }
        
        return $activities;
    }

    // ดึงข้อมูลกิจกรรมตามช่วงเวลา
    public function getActivitiesByDateRange($startDate, $endDate) {
        $sql = "SELECT a.*, at.ActType_Name, m.Maj_name 
                FROM activity a 
                LEFT JOIN activity_type at ON a.ActType_id = at.ActType_id 
                LEFT JOIN major m ON a.Maj_id = m.Maj_id 
                WHERE a.Act_start_date >= :start_date 
                AND a.Act_stop_date <= :end_date
                ORDER BY a.Act_start_date DESC";
        
        $stmt = $this->executeQuery($sql, [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        
        if (!$stmt) {
            return array();
        }
        
        $activities = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $activities[] = $row;
        }
        
        return $activities;
    }

    // ดึงข้อมูลกิจกรรมตามประเภทกิจกรรม
    public function getActivitiesByType($actTypeId) {
        $sql = "SELECT a.*, at.ActType_Name, m.Maj_name 
                FROM activity a 
                LEFT JOIN activity_type at ON a.ActType_id = at.ActType_id 
                LEFT JOIN major m ON a.Maj_id = m.Maj_id 
                WHERE a.ActType_id = :act_type_id
                ORDER BY a.Act_start_date DESC";
        
        $stmt = $this->executeQuery($sql, [':act_type_id' => $actTypeId]);
        
        if (!$stmt) {
            return array();
        }
        
        $activities = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $activities[] = $row;
        }
        
        return $activities;
    }

    // ดึงข้อมูลกิจกรรมตามสถานะ
    public function getActivitiesByStatus($status) {
        $sql = "SELECT a.*, at.ActType_Name, m.Maj_name 
                FROM activity a 
                LEFT JOIN activity_type at ON a.ActType_id = at.ActType_id 
                LEFT JOIN major m ON a.Maj_id = m.Maj_id 
                WHERE a.ActStatus = :status
                ORDER BY a.Act_start_date DESC";
        
        $stmt = $this->executeQuery($sql, [':status' => $status]);
        
        if (!$stmt) {
            return array();
        }
        
        $activities = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $activities[] = $row;
        }
        
        return $activities;
    }

    // นับจำนวนนักศึกษาที่เข้าร่วมกิจกรรม
    public function countParticipantsByActivity($actId) {
        $sql = "SELECT COUNT(*) FROM participation WHERE Act_id = :act_id";
        $stmt = $this->executeQuery($sql, [':act_id' => $actId]);
        
        if ($stmt) {
            return $stmt->fetchColumn();
        }
        
        return 0;
    }

    // ค้นหากิจกรรม
    public function searchActivities($keyword) {
        $sql = "SELECT a.*, at.ActType_Name, m.Maj_name 
                FROM activity a 
                LEFT JOIN activity_type at ON a.ActType_id = at.ActType_id 
                LEFT JOIN major m ON a.Maj_id = m.Maj_id 
                WHERE a.Act_name LIKE :keyword 
                OR a.Act_id LIKE :keyword 
                OR m.Maj_name LIKE :keyword 
                OR at.ActType_Name LIKE :keyword
                ORDER BY a.Act_start_date DESC";
        
        $keyword = "%" . $keyword . "%";
        $stmt = $this->executeQuery($sql, [':keyword' => $keyword]);
        
        if (!$stmt) {
            return array();
        }
        
        $activities = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $activities[] = $row;
        }
        
        return $activities;
    }

    // ค้นหานักศึกษา
    public function searchStudents($keyword) {
        $sql = "SELECT s.*, t.Title_name, m.Maj_name, p.Plan_name, 
                pr.Prof_fname, pr.Prof_lname, pt.Title_name as Prof_title
                FROM student s
                LEFT JOIN title t ON s.Title_id = t.Title_id
                LEFT JOIN major m ON s.Maj_id = m.Maj_id
                LEFT JOIN student_plan p ON s.Plan_id = p.Plan_id
                LEFT JOIN professor pr ON s.Prof_id = pr.Prof_id
                LEFT JOIN title pt ON pr.Title_id = pt.Title_id
                WHERE s.Stu_id LIKE :keyword 
                OR s.Stu_fname LIKE :keyword 
                OR s.Stu_lname LIKE :keyword 
                OR m.Maj_name LIKE :keyword
                ORDER BY s.Stu_id ASC";
        
        $keyword = "%" . $keyword . "%";
        $stmt = $this->executeQuery($sql, [':keyword' => $keyword]);
        
        if (!$stmt) {
            return array();
        }
        
        $students = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $students[] = $row;
        }
        
        return $students;
    }

    // ค้นหาอาจารย์
    public function searchProfessors($keyword) {
        $sql = "SELECT p.*, t.Title_name, m.Maj_name 
                FROM professor p 
                LEFT JOIN title t ON p.Title_id = t.Title_id 
                LEFT JOIN major m ON p.Major_id = m.Maj_id 
                WHERE p.Prof_id LIKE :keyword 
                OR p.Prof_fname LIKE :keyword 
                OR p.Prof_lname LIKE :keyword 
                OR m.Maj_name LIKE :keyword
                ORDER BY p.Prof_id ASC";
        
        $keyword = "%" . $keyword . "%";
        $stmt = $this->executeQuery($sql, [':keyword' => $keyword]);
        
        if (!$stmt) {
            return array();
        }
        
        $professors = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $professors[] = $row;
        }
        
        return $professors;
    }

    // ตรวจสอบการเข้าสู่ระบบ
    public function login($username, $password) {
        if ($this->verifyPassword($username, $password)) {
            $status = $this->getUserStatus($username);
            
            // ดึงข้อมูลผู้ใช้ตามสถานะ
            $userData = null;
            
            if ($status === 'student') {
                $sql = "SELECT l.*, s.* FROM login l 
                        JOIN student s ON l.Stu_id = s.Stu_id 
                        WHERE l.user_id = :username";
                $stmt = $this->executeQuery($sql, [':username' => $username]);
                
                if ($stmt) {
                    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            } elseif ($status === 'professor' || $status === 'admin') {
                $sql = "SELECT l.*, p.* FROM login l 
                        LEFT JOIN professor p ON l.Prof_id = p.Prof_id 
                        WHERE l.user_id = :username";
                $stmt = $this->executeQuery($sql, [':username' => $username]);
                
                if ($stmt) {
                    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }
            
            return [
                'status' => true,
                'user_status' => $status,
                'user_data' => $userData
            ];
        }
        
        return [
            'status' => false,
            'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'
        ];
    }

    // บันทึกล็อกการเข้าสู่ระบบ
    public function logLogin($username, $ipAddress) {
        // เพิ่มฟังก์ชันนี้ถ้าต้องการบันทึกประวัติการเข้าใช้งาน
        // สามารถสร้างตาราง login_logs เพิ่มเติมในฐานข้อมูล
        return true;
    }
    
    /**
     * ดึงข้อมูลกิจกรรมทั้งหมด
     * 
     * @return array ข้อมูลกิจกรรมทั้งหมด
     */
    public function getAllActivities() {
        return $this->getAll('activity', 'Act_id', 'ASC');
    }
}

?>