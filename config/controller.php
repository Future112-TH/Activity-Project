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
    public function getActivityParticipants($actId) {
        try {
            $query = "SELECT s.Stu_id, s.Stu_fname, s.Stu_lname, 
                         m.Maj_name, p.Act_hour, p.CheckIn, p.CheckOut 
                  FROM student s 
                  INNER JOIN major m ON s.Maj_id = m.Maj_id 
                  INNER JOIN participation p ON s.Stu_id = p.Stu_id 
                  WHERE p.Act_id = :act_id 
                  ORDER BY s.Stu_id ASC";
                  
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':act_id', $actId);
            $stmt->execute();
            
            // Debug
            if ($stmt->rowCount() == 0) {
                error_log("No participants found for activity ID: " . $actId);
            }
            
            return $stmt;
        } catch(PDOException $e) {
            $this->logError("Error in getActivityParticipants: " . $e->getMessage());
            return false;
        }
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
        $sql = "SELECT s.*, t.Title_name, m.Maj_name, p.Plan_name, p.Abbre, p.Plan_id,
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
        $sql = "SELECT s.*, t.Title_name, m.Maj_name, f.Fac_name, sp.Plan_name, sp.Abbre  
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
    function insertStudent($stuId, $stuFname, $stuLname, $stuPhone = null, $stuEmail = null, $birthdate = null, $religion = null, $nationality = 'ไทย', $planId, $titleId, $profId, $majId, $curriId = null, $is_status = '2', $pdpa = 'ไม่ยินยอม') {
        try {
            // ตรวจสอบว่ามีนักศึกษานี้ในระบบแล้วหรือไม่
            if ($this->recordExists('student', 'Stu_id', $stuId)) {
                return false;
            }
            
            // เตรียมข้อมูล
            $stuFname = trim($stuFname);
            $stuLname = trim($stuLname);
            $stuPhone = $stuPhone ? trim($stuPhone) : null; // เปลี่ยนเป็น null ถ้าไม่มีข้อมูล
            $stuEmail = $stuEmail ? trim($stuEmail) : null;
            
            // ตรวจสอบว่ามี Curri_id หรือไม่ (จำเป็นตามโครงสร้างฐานข้อมูล)
            $this->logError("Incoming curriId value", $curriId);
    
            // ตรวจสอบว่ามี Curri_id หรือไม่
            if (!$curriId) {
                // ถ้าไม่มี ให้ดึงข้อมูลเดิมมา
                $sql = "SELECT Curri_id, is_status FROM student WHERE Stu_id = :stu_id";
                $stmt = $this->executeQuery($sql, [':stu_id' => $stuId]);
                if ($stmt && $stmt->rowCount() > 0) {
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $curriId = $curriId ?: $result['Curri_id'];
                    $is_status = $is_status ?: $result['is_status'];
                }
            } else {
                // ตรวจสอบว่า curriId ที่ส่งมาอยู่ในฐานข้อมูลหรือไม่
                $sql = "SELECT Curri_id FROM curriculum WHERE Curri_id = :curri_id";
                $stmt = $this->executeQuery($sql, [':curri_id' => $curriId]);
                if (!($stmt && $stmt->rowCount() > 0)) {
                    // ถ้าไม่พบ curri_id ที่ส่งมา ให้ใช้หลักสูตรล่าสุดของสาขา
                    $sql = "SELECT Curri_id FROM curriculum 
                        WHERE Maj_id = :maj_id 
                        ORDER BY Curri_start DESC LIMIT 1";
                    $stmt = $this->executeQuery($sql, [':maj_id' => $majId]);
                    if ($stmt && $stmt->rowCount() > 0) {
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        $oldCurriId = $curriId;
                        $curriId = $result['Curri_id'];
                        $this->logError("Changed curriId from $oldCurriId to", $curriId);
                    }
                }
            }
            
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
                'is_status' => $is_status,
                'pdpa' => $pdpa
            ]);
            
            if ($result) {
                // สร้างบัญชีผู้ใช้สำหรับนักศึกษาใหม่
                $this->createLoginForStudent($stuId);
            }
            
            return $result;
        } catch(PDOException $e) {
            $this->logError("PDOException in insertStudent", $e->getMessage());
            return false;
        }
    }

    // อัปเดตข้อมูลนักศึกษา
    function updateStudent($stuId, $stuFname, $stuLname, $stuPhone = null, $stuEmail = null, $birthdate = null, $religion = null, $nationality = 'ไทย', $planId, $titleId, $profId, $majId, $curriId = null, $is_status = null, $pdpa = null) {
        try {
            // ตรวจสอบว่ามีนักศึกษานี้ในระบบหรือไม่
            if (!$this->recordExists('student', 'Stu_id', $stuId)) {
                return false;
            }
            
            // เตรียมข้อมูล
            $stuFname = trim($stuFname);
            $stuLname = trim($stuLname);
            $stuPhone = $stuPhone ? trim($stuPhone) : null; // เปลี่ยนเป็น null ถ้าไม่มีข้อมูล
            $stuEmail = $stuEmail ? trim($stuEmail) : null;
            
            // ตรวจสอบว่ามี Curri_id และ is_status และ pdpa หรือไม่
            if (!$curriId || !$is_status || !$pdpa) {
                // ถ้าไม่มี ให้ดึงข้อมูลเดิมมา
                $sql = "SELECT Curri_id, is_status, pdpa FROM student WHERE Stu_id = :stu_id";
                $stmt = $this->executeQuery($sql, [':stu_id' => $stuId]);
                if ($stmt && $stmt->rowCount() > 0) {
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $curriId = $curriId ?: $result['Curri_id'];
                    $is_status = $is_status ?: $result['is_status'];
                    $pdpa = $pdpa ?: $result['pdpa'];
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
                'is_status' => $is_status,
                'pdpa' => $pdpa  // เพิ่มฟิลด์ pdpa
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
                            // กรณีเป็นตัวเลข ตรวจสอบว่ามีใน DB จริงหรือไม่
                            $planCheckSql = "SELECT Plan_id FROM student_plan WHERE Plan_id = :plan_id";
                            $planCheckStmt = $this->executeQuery($planCheckSql, [':plan_id' => $planName]);
                            if ($planCheckStmt && $planCheckStmt->rowCount() > 0) {
                                $planId = $planName;
                                error_log("Found plan ID by direct number: $planId");
                            } else {
                                error_log("Numeric plan ID not found in DB: $planName");
                            }
                        }
                        
                        // ถ้ายังไม่ได้ planId ให้ค้นหาจากชื่อ
                        if (empty($planId)) {
                            // ค้นหาจากชื่อเต็มหรือชื่อย่อ แบบเทียบเท่ากัน
                            $sql = "SELECT Plan_id FROM student_plan WHERE Plan_name = :plan_name OR Abbre = :plan_abbre";
                            $stmt = $this->executeQuery($sql, [':plan_name' => $planName, ':plan_abbre' => $planName]);
                            if ($stmt && $stmt->rowCount() > 0) {
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                $planId = $result['Plan_id'];
                                error_log("Found plan ID by exact name: $planId");
                            } else {
                                // ค้นหาแบบคลุมเครือ
                                $sql = "SELECT Plan_id FROM student_plan WHERE Plan_name LIKE :plan_name OR Abbre LIKE :plan_abbre";
                                $stmt = $this->executeQuery($sql, [':plan_name' => "%$planName%", ':plan_abbre' => "%$planName%"]);
                                if ($stmt && $stmt->rowCount() > 0) {
                                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                    $planId = $result['Plan_id'];
                                    error_log("Found plan ID by fuzzy search: $planId");
                                } else {
                                    // ไม่พบแผนการเรียน ให้ใช้แผนแรกในระบบ
                                    $sql = "SELECT Plan_id FROM student_plan ORDER BY Plan_id ASC LIMIT 1";
                                    $stmt = $this->executeQuery($sql);
                                    if ($stmt && $stmt->rowCount() > 0) {
                                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                        $planId = $result['Plan_id'];
                                        error_log("Using default plan ID: $planId");
                                        $error_details[] = "แถวที่ {$row}: ไม่พบแผนการเรียน \"$planName\" ใช้แผนการเรียนเริ่มต้นแทน";
                                    } else {
                                        error_log("No plans found in system");
                                        throw new Exception("ไม่พบแผนการเรียน \"$planName\" ในระบบและไม่มีแผนการเรียนในระบบ");
                                    }
                                }
                            }
                        }
                    } else {
                        // กรณีไม่มีข้อมูลแผนการเรียน ให้ใช้แผนแรกในระบบ
                        $sql = "SELECT Plan_id FROM student_plan ORDER BY Plan_id ASC LIMIT 1";
                        $stmt = $this->executeQuery($sql);
                        if ($stmt && $stmt->rowCount() > 0) {
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            $planId = $result['Plan_id'];
                            error_log("No plan specified, using default: $planId");
                            $error_details[] = "แถวที่ {$row}: ไม่ระบุแผนการเรียน ใช้แผนการเรียนเริ่มต้นแทน";
                        } else {
                            error_log("No plans found in system");
                            throw new Exception("ไม่ระบุแผนการเรียนและไม่มีแผนการเรียนในระบบ");
                        }
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
                    
                    // ตรวจสอบและค้นหา curri_id จากชื่อหลักสูตร
                    $curriId = null;
                    if (!empty($curriName)) {
                        if (is_numeric($curriName)) {
                            // ถ้าเป็นตัวเลข ให้ค้นหาจาก Curri_id โดยตรง
                            $sql = "SELECT Curri_id FROM curriculum WHERE Curri_id = :curri_id";
                            $stmt = $this->executeQuery($sql, [':curri_id' => $curriName]);
                            if ($stmt && $stmt->rowCount() > 0) {
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                $curriId = $result['Curri_id'];
                            } else {
                                // ถ้าไม่พบ ให้ค้นหาจากชื่อหลักสูตร
                                $sql = "SELECT Curri_id FROM curriculum 
                                       WHERE Curri_tname LIKE :curri_name 
                                       OR Curri_t LIKE :curri_name 
                                       OR Curri_ename LIKE :curri_name 
                                       OR Curri_e LIKE :curri_name";
                                $stmt = $this->executeQuery($sql, [':curri_name' => "%$curriName%"]);
                                if ($stmt && $stmt->rowCount() > 0) {
                                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                    $curriId = $result['Curri_id'];
                                } else {
                                    // ถ้าไม่พบ ให้ใช้หลักสูตรล่าสุดของสาขา
                                    $sql = "SELECT Curri_id FROM curriculum 
                                           WHERE Maj_id = :maj_id 
                                           ORDER BY Curri_start DESC LIMIT 1";
                                    $stmt = $this->executeQuery($sql, [':maj_id' => $majId]);
                                    if ($stmt && $stmt->rowCount() > 0) {
                                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                        $curriId = $result['Curri_id'];
                                        $error_details[] = "แถวที่ {$row}: ไม่พบหลักสูตร \"$curriName\" ใช้หลักสูตรล่าสุดของสาขาแทน";
                                    }
                                }
                            }
                        }
                    } else {
                        // กรณีไม่ระบุหลักสูตร ใช้หลักสูตรล่าสุดของสาขา
                        $sql = "SELECT Curri_id FROM curriculum 
                               WHERE Maj_id = :maj_id 
                               ORDER BY Curri_start DESC LIMIT 1";
                        $stmt = $this->executeQuery($sql, [':maj_id' => $majId]);
                        if ($stmt && $stmt->rowCount() > 0) {
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            $curriId = $result['Curri_id'];
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
                                $isStatus,
                                null
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
                                $isStatus,
                                'ไม่ยินยอม'
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
     * @param string $requestType ประเภทการขอเทียบ
     * @param string $requestDetail รายละเอียดการขอเทียบ
     * @param int $actAmount จำนวนครั้ง
     * @param int $actHour จำนวนชั่วโมง
     * @param string $upload ชื่อไฟล์หลักฐาน
     * @param string $actSemester ภาคเรียน
     * @param string $actYear ปีการศึกษา
     * @param string $requestDate วันที่ยื่นคำร้อง
     * @param string $actId รหัสกิจกรรม (ถ้ามี)
     * @param string $studentId รหัสนักศึกษา
     * @return bool ผลการทำงาน
     */

     public function insertComparision($requestType, $requestDetail, $actAmount, $actHour, $upload, $actSemester, $actYear, $requestDate, $actId, $studentId) {
        try {
            $data = [
                'RequestType' => $requestType,
                'RequestDetail' => $requestDetail,
                'Act_amount' => $actAmount,
                'Act_hour' => $actHour,
                'Upload' => $upload,
                'ActSemester' => $actSemester,
                'ActYear' => $actYear,
                'RequestDate' => $requestDate,
                'Status' => 'pending',
                'Stu_id' => $studentId
            ];
            
            // เพิ่ม Act_id ถ้ามีค่า
            if (!empty($actId)) {
                $data['Act_id'] = $actId;
            }
            
            return $this->insert('comparision', $data);
        } catch(PDOException $e) {
            $this->logError("PDOException in insertComparision", $e->getMessage());
            return false;
        }
    }
    
    /**
     * เพิ่มข้อมูลการเข้าร่วมกิจกรรมจากการเทียบโอน
     * 
     * @param string $studentId รหัสนักศึกษา
     * @param string $activityName ชื่อกิจกรรม
     * @param int $actAmount จำนวนครั้ง
     * @param int $actHour จำนวนชั่วโมง
     * @param string $activityCategory ประเภทกิจกรรม (academic, general)
     * @param string $actSemester ภาคเรียน
     * @param string $actYear ปีการศึกษา
     * @return bool ผลการทำงาน
     */
    public function insertParticipation($studentId, $activityName, $actAmount, $actHour, $activityCategory, $actSemester, $actYear) {
        try {
            // สร้างรหัสกิจกรรมใหม่สำหรับกิจกรรมที่เทียบโอน (เช่น COMP-001)
            $query = "SELECT MAX(CAST(SUBSTRING(Act_id, 6) AS UNSIGNED)) as max_id FROM activity WHERE Act_id LIKE 'COMP-%'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $next_id = 1;
            if ($result && $result['max_id']) {
                $next_id = intval($result['max_id']) + 1;
            }
            $actId = 'COMP-' . str_pad($next_id, 3, '0', STR_PAD_LEFT);
            
            // กำหนดประเภทกิจกรรม
            $actTypeId = ($activityCategory === 'academic') ? '2' : '1';
            
            // แปลงปีการศึกษาเป็นวันที่
            $actDate = date('Y-m-d');
            
            // ตรวจสอบรูปแบบปีการศึกษา
            if (is_numeric($actYear)) {
                // กรณีเป็นตัวเลขปีเช่น 2567
                $year = intval($actYear) - 543; // แปลง พ.ศ. เป็น ค.ศ.
                $actDate = $year . '-01-01';
            } else if (is_object($actYear) && $actYear instanceof DateTime) {
                // กรณีเป็น DateTime object
                $actDate = $actYear->format('Y-m-d');
            } else if (is_string($actYear) && strtotime($actYear) !== false) {
                // กรณีเป็นสตริงวันที่
                $actDate = date('Y-m-d', strtotime($actYear));
            }
            
            // เพิ่มกิจกรรมใหม่สำหรับการเทียบโอน
            $activityData = [
                'Act_id' => $actId,
                'Act_name' => '[เทียบโอน] ' . $activityName,
                'Act_hour' => $actHour,
                'Act_start_date' => $actDate,
                'Act_stop_date' => $actDate,
                'ActSemester' => $actSemester,
                'ActStatus' => 'เทียบโอน',
                'ActYear' => $actDate,
                'Maj_id' => '14', // สาขา SWTC เป็นค่าเริ่มต้น
                'ActType_id' => $actTypeId
            ];
            
            $this->insert('activity', $activityData);
            
            // เพิ่มข้อมูลการเข้าร่วม
            $participationData = [
                'Stu_id' => $studentId,
                'Act_id' => $actId,
                'ActSemester' => $actSemester,
                'ActYear' => $actDate,
                'Act_hour' => $actHour,
                'CheckIn' => date('Y-m-d H:i:s'),
                'CheckOut' => date('Y-m-d H:i:s')
            ];
            
            return $this->insert('participation', $participationData);
        } catch(PDOException $e) {
            $this->logError("PDOException in insertParticipation", $e->getMessage());
            return false;
        }
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
    public function deleteComparision($id) {
        try {
            // ดึงข้อมูลไฟล์แนบก่อนลบ
            $stmt = $this->db->prepare("SELECT Upload FROM comparision WHERE Com_id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // ลบไฟล์แนบ (ถ้ามี)
            if ($row && !empty($row['Upload'])) {
                $filePath = __DIR__ . '/../uploads/comparision/' . $row['Upload'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // ลบข้อมูลจากฐานข้อมูล
            $stmt = $this->db->prepare("DELETE FROM comparision WHERE Com_id = ?");
            return $stmt->execute([$id]);
        } catch(PDOException $e) {
            error_log("Error deleting comparision: " . $e->getMessage());
            return false;
        }
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

    /**
     * เพิ่มข้อมูลการขอเทียบกิจกรรม
     * 
     * @param array $data ข้อมูลการขอเทียบกิจกรรม
     * @return bool ผลการทำงาน
     */
    public function addComparision($data) {
        try {
            // แปลงปีการศึกษาเป็นรูปแบบ date
            $year = intval($data['Com_year']) - 543; // แปลง พ.ศ. เป็น ค.ศ.
            $semester = intval($data['Com_semester']);
            
            // กำหนดวันที่ตามภาคเรียน
            switch($semester) {
                case 1: // ภาคเรียนที่ 1 (มิ.ย. - ต.ค.)
                    $date = "$year-06-01";
                    break;
                case 2: // ภาคเรียนที่ 2 (พ.ย. - มี.ค.)
                    $date = "$year-11-01";
                    break;
                case 3: // ภาคเรียนที่ 3 (เม.ย. - พ.ค.)
                    $date = ($year + 1) . "-04-01";
                    break;
                default:
                    $date = "$year-01-01";
            }

            $sql = "INSERT INTO comparision (
                Com_id, Com_name, Com_amount, Com_hour, 
                Com_semester, Com_year, Com_status, Stu_id, Upload
            ) VALUES (
                :Com_id, :Com_name, :Com_amount, :Com_hour,
                :Com_semester, :Com_year, :Com_status, :Stu_id, :Upload
            )";
            
            // Debug log
            error_log("Adding comparision with data: " . print_r($data, true));
            
            $stmt = $this->db->prepare($sql);
            
            // ผูกค่าพารามิเตอร์พร้อมระบุ data type
            $stmt->bindParam(':Com_id', $data['Com_id'], PDO::PARAM_STR);
            $stmt->bindParam(':Com_name', $data['Com_name'], PDO::PARAM_STR);
            $stmt->bindParam(':Com_amount', $data['Com_amount'], PDO::PARAM_INT);
            $stmt->bindParam(':Com_hour', $data['Com_hour'], PDO::PARAM_INT);
            $stmt->bindParam(':Com_semester', $data['Com_semester'], PDO::PARAM_STR);
            $stmt->bindParam(':Com_year', $date, PDO::PARAM_STR);
            $stmt->bindParam(':Com_status', $data['Com_status'], PDO::PARAM_STR);
            $stmt->bindParam(':Stu_id', $data['Stu_id'], PDO::PARAM_STR);
            $stmt->bindParam(':Upload', $data['Upload'], PDO::PARAM_STR);
            
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("SQL Error: ". print_r($stmt->errorInfo(), true));
            }
            
            return $result;
        } catch(PDOException $e) {
            error_log("Error adding comparision: " . $e->getMessage());
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
        $sql = "SELECT c.*, cu.Curri_tname, cu.Curri_t, sp.Plan_name, sp.Abbre, at.ActType_Name 
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
    function insertCriteria($critName, $curriId, $planId, $actTypeId, $critHour, $critAmount = 1) {
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
                'Crit_hour' => $critHour,    // แก้ไขชื่อฟิลด์
                'Crit_amount' => $critAmount  // แก้ไขชื่อฟิลด์
            ]);
        } catch(PDOException $e) {
            $this->logError("PDOException in insertCriteria", $e->getMessage());
            return false;
        }
    }

    /**
     * อัปเดตข้อมูลหลักเกณฑ์
     */
    function updateCriteria($critId, $critName, $curriId, $planId, $actTypeId, $critHour, $critAmount = null) {
        $data = [
            'Crit_name' => $critName,
            'Curri_id' => $curriId,
            'Plan_id' => $planId,
            'ActType_id' => $actTypeId,
            'Crit_hour' => $critHour     // แก้ไขชื่อฟิลด์
        ];
        
        if ($critAmount !== null) {
            $data['Crit_amount'] = $critAmount;  // แก้ไขชื่อฟิลด์
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
    
    // ตรวจสอบการเข้าร่วมกิจกรรม
    public function checkStudentParticipation($studentId, $activityId) {
        try {
            $sql = "SELECT * FROM participation WHERE Stu_id = ? AND Act_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$studentId, $activityId]);
            return $stmt->rowCount() > 0;
        } catch(PDOException $e) {
            $this->logError("Error checking participation", $e->getMessage());
            return false;
        }
    }

    // บันทึกการเข้าร่วมกิจกรรม
    public function addParticipation($studentId, $activityId, $semester, $year, $hours, $checkIn) {
        try {
            $sql = "INSERT INTO participation (Stu_id, Act_id, ActSemester, ActYear, Act_hour, CheckIn) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $studentId,
                $activityId,
                $semester,
                $year, 
                $hours,
                $checkIn
            ]);
        } catch(PDOException $e) {
            $this->logError("Error adding participation", $e->getMessage());
            return false;
        }
    }

    // อัปเดตเวลาเช็คเอาท์
    public function updateParticipationCheckout($studentId, $activityId, $checkOut) {
        try {
            $sql = "UPDATE participation SET CheckOut = ? WHERE Stu_id = ? AND Act_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$checkOut, $studentId, $activityId]);
        } catch(PDOException $e) {
            $this->logError("Error updating checkout time", $e->getMessage());
            return false;
        }
    }

    /**
     * สร้างบัญชีผู้ใช้สำหรับนักศึกษาใหม่
     * 
     * @param string $studentId รหัสนักศึกษา
     * @return bool ผลการทำงาน
     */
    private function createLoginForStudent($studentId) {
        try {
            // ตรวจสอบว่ามีข้อมูลในตาราง login แล้วหรือไม่
            $checkSql = "SELECT user_id FROM login WHERE user_id = :user_id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindParam(':user_id', $studentId);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                // ถ้ามีข้อมูลแล้ว ให้อัปเดตรหัสผ่าน
                $sql = "UPDATE login SET user_pass = :password WHERE user_id = :user_id";
            } else {
                // ถ้ายังไม่มี ให้เพิ่มข้อมูลใหม่
                $sql = "INSERT INTO login (user_id, user_pass, status, time, Stu_id) 
                       VALUES (:user_id, :password, 'student', :time, :stu_id)";
            }

            $stmt = $this->db->prepare($sql);
            
            // ใช้รหัสนักศึกษาเป็นรหัสผ่านเริ่มต้น และเข้ารหัสด้วย MD5
            $password = md5($studentId);
            $currentTime = date('Y-m-d');
            
            $stmt->bindParam(':user_id', $studentId);
            $stmt->bindParam(':password', $password);

            if ($checkStmt->rowCount() == 0) {
                // เพิ่มพารามิเตอร์เพิ่มเติมสำหรับการสร้างบัญชีใหม่
                $stmt->bindParam(':time', $currentTime);
                $stmt->bindParam(':stu_id', $studentId);
            }
            
            $result = $stmt->execute();

            if ($result) {
                $this->logError("Created/Updated login account for student: " . $studentId);
                return true;
            } else {
                $this->logError("Failed to create/update login account for student: " . $studentId);
                return false;
            }

        } catch(PDOException $e) {
            $this->logError("Error creating login for student", $e->getMessage());
            return false;
        }
    }
}
?>