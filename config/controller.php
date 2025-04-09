<?php
class Controller {
    private $db;

    function __construct($conn) {
        $this->db = $conn;
    }

    // ---- FACULTY METHODS ----
    
    // ดึงข้อมูลคณะทั้งหมด
    function getFaculties() {
        try {
            $sql = "SELECT * FROM faculty ORDER BY Fac_id ASC";
            $result = $this->db->query($sql);
            return $result;
        } catch(PDOException $e) {
            echo "Connection Failed: " . $e->getMessage();
            return false;
        }
    }
    
    // ดึงข้อมูลคณะตาม ID
    function getFacultyById($facId) {
        try {
            $sql = "SELECT * FROM faculty WHERE Fac_id = :fac_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':fac_id', $facId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Connection Failed: " . $e->getMessage();
            return false;
        }
    }
    
    // เพิ่มข้อมูลคณะ
    function insertFaculty($facId, $facName) {
        try {
            $sql = "INSERT INTO faculty (Fac_id, Fac_name) VALUES (:fac_id, :fac_name)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':fac_id', $facId);
            $stmt->bindParam(':fac_name', $facName);
            $stmt->execute();
            return true;
        } catch(PDOException $e) {
            echo "Connection Failed: " . $e->getMessage();
            return false;
        }
    }
    
    function updateFaculty($facId, $facName) {
        try {
            $sql = "UPDATE faculty SET Fac_name = :fac_name WHERE Fac_id = :fac_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':fac_id', $facId);
            $stmt->bindParam(':fac_name', $facName);
            $result = $stmt->execute();
            
            // Simple check if faculty exists when no rows were affected
            if ($result && $stmt->rowCount() == 0) {
                $checkSql = "SELECT * FROM faculty WHERE Fac_id = :fac_id";
                $checkStmt = $this->db->prepare($checkSql);
                $checkStmt->bindParam(':fac_id', $facId);
                $checkStmt->execute();
                if ($checkStmt->rowCount() == 0) {
                    return false;
                }
            }
            
            return $result;
        } catch(PDOException $e) {
            return false;
        }
    }
    
    function deleteFaculty($facId) {
        try {
            // Check if faculty exists first
            $checkSql = "SELECT * FROM faculty WHERE Fac_id = :fac_id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindParam(':fac_id', $facId);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() == 0) {
                return false;
            }
            
            $sql = "DELETE FROM faculty WHERE Fac_id = :fac_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':fac_id', $facId);
            return $stmt->execute();
            
        } catch(PDOException $e) {
            return false;
        }
    }
    // ---- MAJOR METHODS ----

    // ดึงข้อมูลสาขาวิชาทั้งหมด
    function getMajors() {
        try {
            $sql = "SELECT m.*, f.Fac_name 
                    FROM major m
                    JOIN faculty f ON m.Fac_id = f.Fac_id
                    ORDER BY m.Maj_id ASC";
            $result = $this->db->query($sql);
            return $result;
        } catch(PDOException $e) {
            echo "Connection Failed: " . $e->getMessage();
            return false;
        }
    }

    // ดึงข้อมูลสาขาวิชาตาม ID
    function getMajorById($majId) {
        try {
            $sql = "SELECT * FROM major WHERE Maj_id = :maj_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':maj_id', $majId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Connection Failed: " . $e->getMessage();
            return false;
        }
    }

    // เพิ่มข้อมูลสาขาวิชา
    function insertMajor($majId, $majName, $facId) {
        try {
            $sql = "INSERT INTO major (Maj_id, Maj_name, Fac_id) VALUES (:maj_id, :maj_name, :fac_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':maj_id', $majId);
            $stmt->bindParam(':maj_name', $majName);
            $stmt->bindParam(':fac_id', $facId);
            $stmt->execute();
            return true;
        } catch(PDOException $e) {
            echo "Connection Failed: " . $e->getMessage();
            return false;
        }
    }

    // อัปเดตข้อมูลสาขาวิชา
    function updateMajor($majId, $majName, $facId) {
        try {
            $sql = "UPDATE major SET Maj_name = :maj_name, Fac_id = :fac_id WHERE Maj_id = :maj_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':maj_id', $majId);
            $stmt->bindParam(':maj_name', $majName);
            $stmt->bindParam(':fac_id', $facId);
            $result = $stmt->execute();
            
            // ตรวจสอบว่ามีสาขาวิชาอยู่จริงหรือไม่
            if ($result && $stmt->rowCount() == 0) {
                $checkSql = "SELECT * FROM major WHERE Maj_id = :maj_id";
                $checkStmt = $this->db->prepare($checkSql);
                $checkStmt->bindParam(':maj_id', $majId);
                $checkStmt->execute();
                if ($checkStmt->rowCount() == 0) {
                    return false;
                }
            }
            
            return $result;
        } catch(PDOException $e) {
            return false;
        }
    }

    // ลบข้อมูลสาขาวิชา
    function deleteMajor($majId) {
        try {
            // ตรวจสอบว่ามีสาขาวิชาอยู่จริงหรือไม่
            $checkSql = "SELECT * FROM major WHERE Maj_id = :maj_id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindParam(':maj_id', $majId);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() == 0) {
                return false;
            }
            
            $sql = "DELETE FROM major WHERE Maj_id = :maj_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':maj_id', $majId);
            return $stmt->execute();
            
        } catch(PDOException $e) {
            return false;
        }
    }

    // ---- ACTIVITY TYPE METHODS ----

    // ดึงข้อมูลประเภทกิจกรรมทั้งหมด
    function getActivityTypes() {
        try {
            $sql = "SELECT * FROM activity_type ORDER BY ActType_id ASC";
            $result = $this->db->query($sql);
            return $result;
        } catch(PDOException $e) {
            echo "Connection Failed: " . $e->getMessage();
            return false;
        }
    }

    // ดึงข้อมูลประเภทกิจกรรมตาม ID
    function getActivityTypeById($id) {
        try {
            $sql = "SELECT * FROM activity_type WHERE ActType_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Connection Failed: " . $e->getMessage();
            return false;
        }
    }

    // เพิ่มข้อมูลประเภทกิจกรรม
    function insertActivityType($id, $name) {
        try {
            $sql = "INSERT INTO activity_type (ActType_id, ActType_Name) VALUES (:id, :name)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            return true;
        } catch(PDOException $e) {
            echo "Connection Failed: " . $e->getMessage();
            return false;
        }
    }

    // อัปเดตข้อมูลประเภทกิจกรรม
    function updateActivityType($id, $name) {
        try {
            $sql = "UPDATE activity_type SET ActType_Name = :name WHERE ActType_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $name);
            $result = $stmt->execute();
            
            // ตรวจสอบว่ามีประเภทกิจกรรมอยู่จริงหรือไม่
            if ($result && $stmt->rowCount() == 0) {
                $checkSql = "SELECT * FROM activity_type WHERE ActType_id = :id";
                $checkStmt = $this->db->prepare($checkSql);
                $checkStmt->bindParam(':id', $id);
                $checkStmt->execute();
                if ($checkStmt->rowCount() == 0) {
                    return false;
                }
            }
            
            return $result;
        } catch(PDOException $e) {
            return false;
        }
    }

    // ลบข้อมูลประเภทกิจกรรม
    function deleteActivityType($id) {
        try {
            // ตรวจสอบว่ามีประเภทกิจกรรมอยู่จริงหรือไม่
            $checkSql = "SELECT * FROM activity_type WHERE ActType_id = :id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() == 0) {
                return false;
            }
            
            $sql = "DELETE FROM activity_type WHERE ActType_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
            
        } catch(PDOException $e) {
            return false;
        }
    }

    // ---- ACTIVITY METHODS ----

    // ดึงข้อมูลกิจกรรมทั้งหมด
    function getActivities() {
        try {
            $sql = "SELECT a.*, at.ActType_Name, m.Maj_name 
                    FROM activity a 
                    LEFT JOIN activity_type at ON a.ActType_id = at.ActType_id 
                    LEFT JOIN major m ON a.Maj_id = m.Maj_id 
                    ORDER BY a.Act_id DESC";
            $result = $this->db->query($sql);
            return $result;
        } catch(PDOException $e) {
            return false;
        }
    }

    // ดึงข้อมูลกิจกรรมตาม ID
    function getActivityById($actId) {
        try {
            $sql = "SELECT a.*, at.ActType_Name, m.Maj_name 
                    FROM activity a 
                    LEFT JOIN activity_type at ON a.ActType_id = at.ActType_id 
                    LEFT JOIN major m ON a.Maj_id = m.Maj_id 
                    WHERE a.Act_id = :act_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':act_id', $actId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
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
            $sql = "INSERT INTO activity (Act_id, Act_name, Act_hour, Act_start_date, Act_stop_date, ActSemester, ActStatus, ActYear, Maj_id, ActType_id) 
                    VALUES (:act_id, :act_name, :act_hour, :act_start_date, :act_stop_date, :act_semester, :act_status, :act_year, :maj_id, :act_type_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':act_id', $act_id);
            $stmt->bindParam(':act_name', $act_name);
            $stmt->bindParam(':act_hour', $act_hour);
            $stmt->bindParam(':act_start_date', $act_start_date);
            $stmt->bindParam(':act_stop_date', $act_stop_date);
            $stmt->bindParam(':act_semester', $act_semester);
            $stmt->bindParam(':act_status', $act_status);
            $stmt->bindParam(':act_year', $act_year);
            $stmt->bindParam(':maj_id', $maj_id);
            $stmt->bindParam(':act_type_id', $act_type_id);
            
            $result = $stmt->execute();
            
            // เพิ่มการบันทึก log
            if (!$result) {
                error_log("Insert activity failed: " . print_r($stmt->errorInfo(), true));
            } else {
                error_log("Activity created with ID: " . $act_id);
            }
            
            return $result;
        } catch(PDOException $e) {
            error_log("PDOException in insertActivity: " . $e->getMessage());
            return false;
        }
    }

    // อัปเดตข้อมูลกิจกรรม
    function updateActivity($act_id, $act_name, $act_hour, $act_start_date, $act_stop_date, $act_semester, $act_status, $act_year, $act_type_id, $maj_id) {
        try {
            $sql = "UPDATE activity 
                    SET Act_name = :act_name, 
                        Act_hour = :act_hour, 
                        Act_start_date = :act_start_date, 
                        Act_stop_date = :act_stop_date, 
                        ActSemester = :act_semester, 
                        ActStatus = :act_status, 
                        ActYear = :act_year, 
                        Maj_id = :maj_id, 
                        ActType_id = :act_type_id 
                    WHERE Act_id = :act_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':act_id', $act_id);
            $stmt->bindParam(':act_name', $act_name);
            $stmt->bindParam(':act_hour', $act_hour);
            $stmt->bindParam(':act_start_date', $act_start_date);
            $stmt->bindParam(':act_stop_date', $act_stop_date);
            $stmt->bindParam(':act_semester', $act_semester);
            $stmt->bindParam(':act_status', $act_status);
            $stmt->bindParam(':act_year', $act_year);
            $stmt->bindParam(':maj_id', $maj_id);
            $stmt->bindParam(':act_type_id', $act_type_id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    // ลบข้อมูลกิจกรรม
    function deleteActivity($act_id) {
        try {
            $sql = "DELETE FROM activity WHERE Act_id = :act_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':act_id', $act_id);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    // ดึงนักศึกษาที่เข้าร่วมกิจกรรม
    function getActivityParticipants($act_id) {
        try {
            $sql = "SELECT p.*, s.Stu_fname, s.Stu_lname, m.Maj_name 
                    FROM participation p 
                    JOIN student s ON p.Stu_id = s.Stu_id 
                    JOIN major m ON s.Maj_id = m.Maj_id 
                    WHERE p.Act_id = :act_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':act_id', $act_id);
            $stmt->execute();
            
            return $stmt;
        } catch(PDOException $e) {
            return false;
        }
    }

    // ---- ADVISOR METHODS ----

    function getProfessors() {
        try {
            // สร้าง SQL query
            $sql = "SELECT p.*, t.Title_name, m.Maj_name 
                    FROM professor p 
                    LEFT JOIN title t ON p.Title_id = t.Title_id 
                    LEFT JOIN major m ON p.Major_id = m.Maj_id 
                    ORDER BY p.Prof_id ASC";
            
            $result = $this->db->query($sql);
            return $result;
        } catch(PDOException $e) {
            error_log("Error in getProfessors: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ดึงข้อมูลอาจารย์ที่ปรึกษาตาม ID
     * @param int $profId รหัสอาจารย์
     * @return array|false
     */
    function getProfessorById($profId) {
        try {
            $sql = "SELECT p.*, t.Title_name, m.Maj_name 
                    FROM professor p 
                    LEFT JOIN title t ON p.Title_id = t.Title_id 
                    LEFT JOIN major m ON p.Major_id = m.Maj_id 
                    WHERE p.Prof_id = :prof_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':prof_id', $profId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error in getProfessorById: " . $e->getMessage());
            return false;
        }
    }
    
    // เพิ่มข้อมูลอาจารย์ที่ปรึกษา
    function insertProfessor($prof_id, $prof_fname, $prof_lname, $phone, $email, $title_id, $major_id) {
        try {
            // ตรวจสอบและแปลงค่าที่จำเป็น
            $prof_id = trim($prof_id);
            $prof_fname = trim($prof_fname);
            $prof_lname = trim($prof_lname);
            $phone = $phone ? trim($phone) : null;
            $email = $email ? trim($email) : null;
            $status = trim($status);
            $title_id = trim($title_id);
            $major_id = trim($major_id);
            
            $sql = "INSERT INTO professor (Prof_id, Prof_fname, Prof_lname, Phone, Email, Title_id, Major_id) 
                    VALUES (:prof_id, :prof_fname, :prof_lname, :phone, :email, :title_id, :major_id)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':prof_id', $prof_id);
            $stmt->bindParam(':prof_fname', $prof_fname);
            $stmt->bindParam(':prof_lname', $prof_lname);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':title_id', $title_id);
            $stmt->bindParam(':major_id', $major_id);
            
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Error in insertProfessor: " . print_r($stmt->errorInfo(), true));
            }
            
            return $result;
        } catch(PDOException $e) {
            error_log("PDOException in insertProfessor: " . $e->getMessage());
            return false;
        }
    }
    
    // อัปเดตข้อมูลอาจารย์ที่ปรึกษา
    function updateProfessor($prof_id, $prof_fname, $prof_lname, $phone, $email, $title_id, $major_id) {
        try {
            // ตรวจสอบและแปลงค่าที่จำเป็น
            $prof_id = trim($prof_id);
            $prof_fname = trim($prof_fname);
            $prof_lname = trim($prof_lname);
            $phone = $phone ? trim($phone) : null;
            $email = $email ? trim($email) : null;
            $title_id = trim($title_id);
            $major_id = trim($major_id);
            
            $sql = "UPDATE professor 
                    SET Prof_fname = :prof_fname, 
                        Prof_lname = :prof_lname, 
                        Phone = :phone, 
                        Email = :email,
                        Title_id = :title_id, 
                        Major_id = :major_id 
                    WHERE Prof_id = :prof_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':prof_id', $prof_id);
            $stmt->bindParam(':prof_fname', $prof_fname);
            $stmt->bindParam(':prof_lname', $prof_lname);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':title_id', $title_id);
            $stmt->bindParam(':major_id', $major_id);
            
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Error in updateProfessor: " . print_r($stmt->errorInfo(), true));
            }
            
            return $result;
        } catch(PDOException $e) {
            error_log("PDOException in updateProfessor: " . $e->getMessage());
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
                error_log("Cannot delete professor ID: $prof_id, has $count students");
                $_SESSION['error'] = "ไม่สามารถลบข้อมูลได้ เนื่องจากมีนักศึกษาในที่ปรึกษา $count คน";
                return false;
            }
            
            $sql = "DELETE FROM professor WHERE Prof_id = :prof_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':prof_id', $prof_id);
            
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Error in deleteProfessor: " . print_r($stmt->errorInfo(), true));
            }
            
            return $result;
        } catch(PDOException $e) {
            error_log("PDOException in deleteProfessor: " . $e->getMessage());
            return false;
        }
    }
    
    // ดึงข้อมูลคำนำหน้าจาก ID

    public function getTitleById($title_id) {
        try {
            $query = "SELECT * FROM title WHERE Title_id = :title_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':title_id', $title_id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error in getTitleById: " . $e->getMessage());
            return false;
        }
    }
    
    // ดึงข้อมูลคำนำหน้าทั้งหมด
    function getTitles() {
        try {
            $sql = "SELECT * FROM title ORDER BY Title_id ASC";
            $result = $this->db->query($sql);
            return $result;
        } catch(PDOException $e) {
            error_log("Error in getTitles: " . $e->getMessage());
            return false;
        }
    }
    
    // ดึงนักศึกษาที่อยู่ภายใต้การดูแลของอาจารย์ที่ปรึกษา
    function getAdvisoryStudents($profId) {
        try {
            $sql = "SELECT s.*, t.Title_name, m.Maj_name, p.Plan_name
                    FROM student s
                    LEFT JOIN title t ON s.Title_id = t.Title_id
                    LEFT JOIN major m ON s.Maj_id = m.Maj_id
                    LEFT JOIN student_plan p ON s.Plan_id = p.Plan_id
                    WHERE s.Prof_id = :prof_id
                    ORDER BY s.Stu_id ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':prof_id', $profId);
            $stmt->execute();
            
            return $stmt;
        } catch(PDOException $e) {
            error_log("Error in getAdvisoryStudents: " . $e->getMessage());
            return false;
        }
    }

    // ---- STUDENT METHODS ----

    function getStudents() {
        try {
            $sql = "SELECT s.*, t.Title_name, m.Maj_name, p.Plan_name, 
                    pr.Prof_fname, pr.Prof_lname, pt.Title_name as Prof_title
                    FROM student s
                    LEFT JOIN title t ON s.Title_id = t.Title_id
                    LEFT JOIN major m ON s.Maj_id = m.Maj_id
                    LEFT JOIN student_plan p ON s.Plan_id = p.Plan_id
                    LEFT JOIN professor pr ON s.Prof_id = pr.Prof_id
                    LEFT JOIN title pt ON pr.Title_id = pt.Title_id
                    ORDER BY s.Stu_id ASC";
            $stmt = $this->db->query($sql);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Error in getStudents: " . $e->getMessage());
            return false;
        }
    }

    function getStudentById($stu_id){
        try {
            $sql = "SELECT s.*, t.Title_name, m.Maj_name, f.Fac_name, sp.Plan_name  
                    FROM student s
                    LEFT JOIN title t ON s.Title_id = t.Title_id
                    LEFT JOIN major m ON s.Maj_id = m.Maj_id
                    LEFT JOIN faculty f ON m.Fac_id = f.Fac_id
                    LEFT JOIN student_plan sp ON s.Plan_id = sp.Plan_id
                    WHERE s.Stu_id = :stu_id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':stu_id', $stu_id);
            
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching student data: " . $e->getMessage());
            return false;
        }
    }

    //ดึงข้อมูลแผนการเรียน

    function getStudentPlans() {
        try {
            $sql = "SELECT * FROM student_plan ORDER BY Plan_id ASC";
            $stmt = $this->db->query($sql);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Error in getStudentPlans: " . $e->getMessage());
            return false;
        }
    }


    // ดึงกิจกรรมที่นักศึกษาเข้าร่วม

    function getStudentActivities($stuId) {
        try {
            $sql = "SELECT p.*, a.Act_name, a.Act_start_date, a.Act_stop_date, a.Act_hour, 
                    at.ActType_Name, at.ActType_id
                    FROM participation p
                    JOIN activity a ON p.Act_id = a.Act_id
                    LEFT JOIN activity_type at ON a.ActType_id = at.ActType_id
                    WHERE p.Stu_id = :stu_id
                    ORDER BY a.Act_start_date DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':stu_id', $stuId);
            $stmt->execute();
            
            return $stmt;
        } catch(PDOException $e) {
            error_log("Error in getStudentActivities: " . $e->getMessage());
            return false;
        }
    }

    // เพิ่มข้อมูลนักศึกษา

    function insertStudent($stuId, $stuFname, $stuLname, $stuPhone, $stuEmail = null, $birthdate = null, $religion = null, $nationality = 'ไทย', $planId, $titleId, $profId, $majId) {
        try {
            // ตรวจสอบว่ามีนักศึกษานี้ในระบบแล้วหรือไม่
            $existingStudent = $this->getStudentById($stuId);
            if ($existingStudent) {
                return false;
            }
            
            // เตรียมข้อมูล
            $stuFname = trim($stuFname);
            $stuLname = trim($stuLname);
            $stuPhone = trim($stuPhone);
            $stuEmail = $stuEmail ? trim($stuEmail) : null;
            
            $sql = "INSERT INTO student 
                    (Stu_id, Stu_fname, Stu_lname, Stu_phone, Stu_email, Birthdate, Religion, Nationality, Plan_id, Title_id, Prof_id, Maj_id) 
                    VALUES 
                    (:stu_id, :stu_fname, :stu_lname, :stu_phone, :stu_email, :birthdate, :religion, :nationality, :plan_id, :title_id, :prof_id, :maj_id)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':stu_id', $stuId);
            $stmt->bindParam(':stu_fname', $stuFname);
            $stmt->bindParam(':stu_lname', $stuLname);
            $stmt->bindParam(':stu_phone', $stuPhone);
            $stmt->bindParam(':stu_email', $stuEmail);
            $stmt->bindParam(':birthdate', $birthdate);
            $stmt->bindParam(':religion', $religion);
            $stmt->bindParam(':nationality', $nationality);
            $stmt->bindParam(':plan_id', $planId);
            $stmt->bindParam(':title_id', $titleId);
            $stmt->bindParam(':prof_id', $profId);
            $stmt->bindParam(':maj_id', $majId);
            
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Error in insertStudent: " . json_encode($stmt->errorInfo()));
            }
            
            return $result;
        } catch(PDOException $e) {
            error_log("PDOException in insertStudent: " . $e->getMessage());
            return false;
        }
    }

    // อัปเดตข้อมูลนักศึกษา

    function updateStudent($stuId, $stuFname, $stuLname, $stuPhone, $stuEmail = null, $birthdate = null, $religion = null, $nationality = 'ไทย', $planId, $titleId, $profId, $majId) {
        try {
            // ตรวจสอบว่ามีนักศึกษานี้ในระบบหรือไม่
            $existingStudent = $this->getStudentById($stuId);
            if (!$existingStudent) {
                return false;
            }
            
            // เตรียมข้อมูล
            $stuFname = trim($stuFname);
            $stuLname = trim($stuLname);
            $stuPhone = trim($stuPhone);
            $stuEmail = $stuEmail ? trim($stuEmail) : null;
            
            $sql = "UPDATE student SET 
                    Stu_fname = :stu_fname,
                    Stu_lname = :stu_lname,
                    Stu_phone = :stu_phone,
                    Stu_email = :stu_email,
                    Birthdate = :birthdate,
                    Religion = :religion,
                    Nationality = :nationality,
                    Plan_id = :plan_id,
                    Title_id = :title_id,
                    Prof_id = :prof_id,
                    Maj_id = :maj_id
                    WHERE Stu_id = :stu_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':stu_id', $stuId);
            $stmt->bindParam(':stu_fname', $stuFname);
            $stmt->bindParam(':stu_lname', $stuLname);
            $stmt->bindParam(':stu_phone', $stuPhone);
            $stmt->bindParam(':stu_email', $stuEmail);
            $stmt->bindParam(':birthdate', $birthdate);
            $stmt->bindParam(':religion', $religion);
            $stmt->bindParam(':nationality', $nationality);
            $stmt->bindParam(':plan_id', $planId);
            $stmt->bindParam(':title_id', $titleId);
            $stmt->bindParam(':prof_id', $profId);
            $stmt->bindParam(':maj_id', $majId);
            
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Error in updateStudent: " . json_encode($stmt->errorInfo()));
            }
            
            return $result;
        } catch(PDOException $e) {
            error_log("PDOException in updateStudent: " . $e->getMessage());
            return false;
        }
    }

    // ลบข้อมูลนักศึกษา

    function deleteStudent($stuId) {
        try {
            // ตรวจสอบว่ามีนักศึกษานี้ในระบบหรือไม่
            $existingStudent = $this->getStudentById($stuId);
            if (!$existingStudent) {
                return false;
            }
            
            // เริ่ม transaction
            $this->db->beginTransaction();
            
            // ลบข้อมูลการเข้าร่วมกิจกรรมของนักศึกษา (ถ้ามี)
            $deleteParticipation = "DELETE FROM participation WHERE Stu_id = :stu_id";
            $stmtParticipation = $this->db->prepare($deleteParticipation);
            $stmtParticipation->bindParam(':stu_id', $stuId);
            $stmtParticipation->execute();
            
            // ลบข้อมูลการเทียบโอนกิจกรรม (ถ้ามี)
            $deleteComparision = "DELETE FROM comparision WHERE Stu_id = :stu_id";
            $stmtComparision = $this->db->prepare($deleteComparision);
            $stmtComparision->bindParam(':stu_id', $stuId);
            $stmtComparision->execute();
            
            // ลบข้อมูลนักศึกษา
            $deleteStudent = "DELETE FROM student WHERE Stu_id = :stu_id";
            $stmtStudent = $this->db->prepare($deleteStudent);
            $stmtStudent->bindParam(':stu_id', $stuId);
            $resultStudent = $stmtStudent->execute();
            
            if ($resultStudent) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                error_log("Error in deleteStudent: " . json_encode($stmtStudent->errorInfo()));
                return false;
            }
        } catch(PDOException $e) {
            $this->db->rollBack();
            error_log("PDOException in deleteStudent: " . $e->getMessage());
            return false;
        }
    }

    // นำเข้าข้อมูลนักศึกษาจากไฟล์ Excel

    function importStudentsFromExcel($file) {
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
            require_once 'includes/vendor/autoload.php'; // ต้องติดตั้ง PhpSpreadsheet ก่อน
            
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($tmpFile);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($tmpFile);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // เริ่ม transaction
            $this->db->beginTransaction();
            
            $rowCount = $worksheet->getHighestRow();
            $colCount = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($worksheet->getHighestColumn());
            
            $inserted = 0;
            $errors = 0;
            
            // เริ่มอ่านข้อมูลจากแถวที่ 2 (ข้ามหัวตาราง)
            for ($row = 2; $row <= $rowCount; $row++) {
                // อ่านข้อมูลจากแต่ละคอลัมน์
                $stuId = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                $titleId = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $stuFname = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                $stuLname = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                $stuPhone = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                $stuEmail = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                $birthdate = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                $religion = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
                $nationality = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                $planId = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                $majId = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
                $profId = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
                
                // ตรวจสอบข้อมูลสำคัญ
                if (empty($stuId) || empty($titleId) || empty($stuFname) || empty($stuLname) || 
                    empty($stuPhone) || empty($planId) || empty($majId) || empty($profId)) {
                    $errors++;
                    continue;
                }
                
                // แปลงรูปแบบวันที่ (ถ้ามี)
                if (!empty($birthdate)) {
                    if ($birthdate instanceof \DateTime) {
                        $birthdate = $birthdate->format('Y-m-d');
                    } elseif (is_numeric($birthdate)) {
                        // แปลง Excel date (serial number) เป็น PHP date
                        $birthdate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($birthdate)->format('Y-m-d');
                    }
                } else {
                    $birthdate = null;
                }
                
                // ตรวจสอบว่ามีนักศึกษานี้ในระบบแล้วหรือไม่
                $existingStudent = $this->getStudentById($stuId);
                
                if ($existingStudent) {
                    // อัปเดตข้อมูลนักศึกษา
                    $result = $this->updateStudent(
                        $stuId, $stuFname, $stuLname, $stuPhone, $stuEmail,
                        $birthdate, $religion, $nationality, $planId, $titleId, $profId, $majId
                    );
                } else {
                    // เพิ่มข้อมูลนักศึกษาใหม่
                    $result = $this->insertStudent(
                        $stuId, $stuFname, $stuLname, $stuPhone, $stuEmail,
                        $birthdate, $religion, $nationality, $planId, $titleId, $profId, $majId
                    );
                }
                
                if ($result) {
                    $inserted++;
                } else {
                    $errors++;
                }
            }
            
            // Commit transaction ถ้าสำเร็จ
            $this->db->commit();
            
            return [
                'status' => true,
                'inserted' => $inserted,
                'errors' => $errors,
                'message' => "นำเข้าข้อมูลสำเร็จ $inserted รายการ, เกิดข้อผิดพลาด $errors รายการ"
            ];
        } catch(\Exception $e) {
            // Rollback ถ้าเกิดข้อผิดพลาด
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            
            error_log("Exception in importStudentsFromExcel: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ดึงข้อมูลการขอเทียบกิจกรรมตามรหัสนักศึกษา
     * 
     * @param string $studentId รหัสนักศึกษา
     * @return array ข้อมูลการขอเทียบกิจกรรม
     */
    public function getComparisionsByStudentId($studentId) {
        try {
            $query = "SELECT * FROM comparision WHERE Stu_id = :student_id ORDER BY RequestDate DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":student_id", $studentId);
            $stmt->execute();
                
            $comparisions = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comparisions[] = $row;
            }
            
            return $comparisions;
        } catch (PDOException $e) {
            error_log("Get comparisions error: " . $e->getMessage());
            return array();
        }
    }

    /**
     * ดึงข้อมูลการขอเทียบกิจกรรมตามรหัสคำร้อง
     * 
     * @param int $comId รหัสคำร้อง
     * @return array|bool ข้อมูลการขอเทียบกิจกรรมหรือ false ถ้าไม่พบ
     */
    public function getComparisionById($comId) {
        try {
            $query = "SELECT * FROM comparision WHERE Com_id = :com_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":com_id", $comId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get comparision by ID error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * เพิ่มข้อมูลการขอเทียบกิจกรรม
     * 
     * @param string $requestType ประเภทการขอเทียบ (position, award, helper)
     * @param string $requestDetail รายละเอียดการขอเทียบ
     * @param int $actAmount จำนวนครั้งที่เข้าร่วม
     * @param int $actHour จำนวนชั่วโมง
     * @param string $upload ชื่อไฟล์เอกสารแนบ
     * @param string $actSemester ภาคเรียน (เช่น 1/2567)
     * @param string $actYear ปีการศึกษา
     * @param string $requestDate วันที่ยื่นคำร้อง
     * @param string $actId รหัสกิจกรรม (ถ้ามี)
     * @param string $studentId รหัสนักศึกษา
     * @return bool สถานะการทำงาน
     */
    public function insertComparision($requestType, $requestDetail, $actAmount, $actHour, $upload, $actSemester, $actYear, $requestDate, $actId, $studentId) {
        try {
            $query = "INSERT INTO comparision (RequestType, RequestDetail, Act_amount, Act_hour, Upload, ActSemester, ActYear, RequestDate, Act_id, Stu_id, Status) 
                    VALUES (:request_type, :request_detail, :act_amount, :act_hour, :upload, :act_semester, :act_year, :request_date, :act_id, :student_id, 'pending')";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":request_type", $requestType);
            $stmt->bindParam(":request_detail", $requestDetail);
            $stmt->bindParam(":act_amount", $actAmount);
            $stmt->bindParam(":act_hour", $actHour);
            $stmt->bindParam(":upload", $upload);
            $stmt->bindParam(":act_semester", $actSemester);
            $stmt->bindParam(":act_year", $actYear);
            $stmt->bindParam(":request_date", $requestDate);
            $stmt->bindParam(":act_id", $actId);
            $stmt->bindParam(":student_id", $studentId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Insert comparision error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * อัปเดตข้อมูลการขอเทียบกิจกรรม
     * 
     * @param int $comId รหัสคำร้อง
     * @param string $requestType ประเภทการขอเทียบ (position, award, helper)
     * @param string $requestDetail รายละเอียดการขอเทียบ
     * @param int $actAmount จำนวนครั้งที่เข้าร่วม
     * @param int $actHour จำนวนชั่วโมง
     * @param string $upload ชื่อไฟล์เอกสารแนบ
     * @param string $actSemester ภาคเรียน (เช่น 1/2567)
     * @param string $actYear ปีการศึกษา
     * @param string $actId รหัสกิจกรรม (ถ้ามี)
     * @param string $studentId รหัสนักศึกษา
     * @return bool สถานะการทำงาน
     */
    public function updateComparision($comId, $requestType, $requestDetail, $actAmount, $actHour, $upload, $actSemester, $actYear, $actId, $studentId) {
        try {
            $query = "UPDATE comparision 
                    SET RequestType = :request_type,
                        RequestDetail = :request_detail,
                        Act_amount = :act_amount, 
                        Act_hour = :act_hour, 
                        Upload = :upload, 
                        ActSemester = :act_semester, 
                        ActYear = :act_year, 
                        Act_id = :act_id,
                        Status = 'pending'
                    WHERE Com_id = :com_id AND Stu_id = :student_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":com_id", $comId);
            $stmt->bindParam(":request_type", $requestType);
            $stmt->bindParam(":request_detail", $requestDetail);
            $stmt->bindParam(":act_amount", $actAmount);
            $stmt->bindParam(":act_hour", $actHour);
            $stmt->bindParam(":upload", $upload);
            $stmt->bindParam(":act_semester", $actSemester);
            $stmt->bindParam(":act_year", $actYear);
            $stmt->bindParam(":act_id", $actId);
            $stmt->bindParam(":student_id", $studentId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update comparision error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ลบข้อมูลการขอเทียบกิจกรรม
     * 
     * @param int $comId รหัสคำร้อง
     * @return bool สถานะการทำงาน
     */
    public function deleteComparision($comId) {
        try {
            $query = "DELETE FROM comparision WHERE Com_id = :com_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":com_id", $comId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Delete comparision error: " . $e->getMessage());
            return false;
        }
    }

    // อัปเดตสถานะคำร้องขอเทียบกิจกรรม

    public function updateComparisionStatus($comId, $status, $comment = '', $approvedBy = '') {
        try {
            $approvedDate = null;
            if ($status === 'approved' || $status === 'rejected') {
                $approvedDate = date('Y-m-d H:i:s');
            }
            
            $query = "UPDATE comparision 
                    SET Status = :status, 
                        Comment = :comment, 
                        ApprovedBy = :approved_by, 
                        ApprovedDate = :approved_date 
                    WHERE Com_id = :com_id";
                    
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":com_id", $comId);
            $stmt->bindParam(":status", $status);
            $stmt->bindParam(":comment", $comment);
            $stmt->bindParam(":approved_by", $approvedBy);
            $stmt->bindParam(":approved_date", $approvedDate);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update comparision status error: " . $e->getMessage());
            return false;
        }
    }

    // ดึงข้อมูลกิจกรรมทั้งหมด

    public function getAllActivities() {
        try {
            $query = "SELECT * FROM activity ORDER BY Act_id ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $activities = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $activities[] = $row;
            }
            
            return $activities;
        } catch (PDOException $e) {
            error_log("Get all activities error: " . $e->getMessage());
            return array();
        }
    }

    // ดึงข้อมูลการขอเทียบกิจกรรมที่อนุมัติแล้วของนักศึกษา

    public function getApprovedComparisionsByStudentId($studentId) {
        try {
            $query = "SELECT * FROM comparision WHERE Stu_id = :student_id AND Status = 'approved' ORDER BY RequestDate DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":student_id", $studentId);
            $stmt->execute();
            
            $comparisions = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comparisions[] = $row;
            }
            
            return $comparisions;
        } catch (PDOException $e) {
            error_log("Get approved comparisions error: " . $e->getMessage());
            return array();
        }
    }

    // ดึงข้อมูลคำร้องขอเทียบกิจกรรมที่รอการอนุมัติทั้งหมด (สำหรับผู้ดูแลระบบ)

    public function getPendingComparisions() {
        try {
            $query = "SELECT c.*, s.Stu_fname, s.Stu_lname, s.Maj_id, t.Title_name 
                    FROM comparision c 
                    LEFT JOIN student s ON c.Stu_id = s.Stu_id 
                    LEFT JOIN title t ON s.Title_id = t.Title_id 
                    WHERE c.Status = 'pending' 
                    ORDER BY c.RequestDate DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $comparisions = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comparisions[] = $row;
            }
            
            return $comparisions;
        } catch (PDOException $e) {
            error_log("Get pending comparisions error: " . $e->getMessage());
            return array();
        }
    }

    // ดึงข้อมูลคำร้องขอเทียบกิจกรรมตามสถานะ (สำหรับผู้ดูแลระบบ)

    public function getComparisionsByStatus($status = 'all') {
        try {
            $query = "SELECT c.*, s.Stu_fname, s.Stu_lname, s.Maj_id, t.Title_name 
                    FROM comparision c 
                    LEFT JOIN student s ON c.Stu_id = s.Stu_id 
                    LEFT JOIN title t ON s.Title_id = t.Title_id ";
            
            if ($status !== 'all') {
                $query .= "WHERE c.Status = :status ";
            }
            
            $query .= "ORDER BY c.RequestDate DESC";
            
            $stmt = $this->db->prepare($query);
            
            if ($status !== 'all') {
                $stmt->bindParam(":status", $status);
            }
            
            $stmt->execute();
            
            $comparisions = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comparisions[] = $row;
            }
            
            return $comparisions;
        } catch (PDOException $e) {
            error_log("Get comparisions by status error: " . $e->getMessage());
            return array();
        }
    }

    // ดึงข้อมูลคำร้องขอเทียบกิจกรรมตามประเภทการขอเทียบ (สำหรับผู้ดูแลระบบ)

    public function getComparisionsByType($requestType = 'all') {
        try {
            $query = "SELECT c.*, s.Stu_fname, s.Stu_lname, s.Maj_id, t.Title_name 
                    FROM comparision c 
                    LEFT JOIN student s ON c.Stu_id = s.Stu_id 
                    LEFT JOIN title t ON s.Title_id = t.Title_id ";
            
            if ($requestType !== 'all') {
                $query .= "WHERE c.RequestType = :request_type ";
            }
            
            $query .= "ORDER BY c.RequestDate DESC";
            
            $stmt = $this->db->prepare($query);
            
            if ($requestType !== 'all') {
                $stmt->bindParam(":request_type", $requestType);
            }
            
            $stmt->execute();
            
            $comparisions = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comparisions[] = $row;
            }
            
            return $comparisions;
        } catch (PDOException $e) {
            error_log("Get comparisions by type error: " . $e->getMessage());
            return array();
        }
    }

    // ดึงจำนวนคำร้องขอเทียบกิจกรรมตามสถานะ (สำหรับหน้าแดชบอร์ด)

    public function getComparisionStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN Status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN Status = 'approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN Status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                        SUM(CASE WHEN RequestType = 'position' THEN 1 ELSE 0 END) as position,
                        SUM(CASE WHEN RequestType = 'award' THEN 1 ELSE 0 END) as award,
                        SUM(CASE WHEN RequestType = 'helper' THEN 1 ELSE 0 END) as helper
                    FROM comparision";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get comparision stats error: " . $e->getMessage());
            return array(
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
                'position' => 0,
                'award' => 0,
                'helper' => 0
            );
        }
    }

    // ดึงข้อมูลนักศึกษาพร้อมคำนำหน้าชื่อตามรหัสนักศึกษา

    public function getStudentWithTitleById($studentId) {
        try {
            $query = "SELECT s.*, t.Title_name 
                    FROM student s 
                    LEFT JOIN title t ON s.Title_id = t.Title_id 
                    WHERE s.Stu_id = :student_id";
           $stmt = $this->db->prepare($query);
            $stmt->bindParam(":student_id", $studentId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get student with title error: " . $e->getMessage());
            return false;
        }
    }

    // ค้นหาข้อมูลคำร้องขอเทียบกิจกรรม (สำหรับผู้ดูแลระบบ)

    public function searchComparisions($keyword) {
        try {
            $query = "SELECT c.*, s.Stu_fname, s.Stu_lname, s.Maj_id, t.Title_name 
                    FROM comparision c 
                    LEFT JOIN student s ON c.Stu_id = s.Stu_id 
                    LEFT JOIN title t ON s.Title_id = t.Title_id 
                    WHERE c.Stu_id LIKE :keyword 
                    OR s.Stu_fname LIKE :keyword 
                    OR s.Stu_lname LIKE :keyword 
                    OR c.RequestDetail LIKE :keyword 
                    ORDER BY c.RequestDate DESC";
            
            $keyword = "%" . $keyword . "%";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":keyword", $keyword);
            $stmt->execute();
            
            $comparisions = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comparisions[] = $row;
            }
            
            return $comparisions;
        } catch (PDOException $e) {
            error_log("Search comparisions error: " . $e->getMessage());
            return array();
        }
    }

    // USER METHODS

    // ดึงข้อมูลสถานะผู้ใช้จากตาราง login

    public function getUserStatus($user_id) {
        try {
            $query = "SELECT status FROM login WHERE user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['status'];
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error in getUserStatus: " . $e->getMessage());
            return false;
        }
    }

    // เมธอดสำหรับตรวจสอบรหัสผ่าน
    public function verifyPassword($user_id, $password) {
        try {
            $query = "SELECT user_pass FROM login WHERE user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $stored_password = $row['user_pass'];
                
                // ลองหลายวิธีการตรวจสอบรหัสผ่าน
                // 1. ถ้าใช้ password_hash (PHP ปัจจุบัน)
                if (password_verify($password, $stored_password)) {
                    return true;
                }
                // 2. ถ้าใช้ MD5 (วิธีเก่า)
                else if (md5($password) === $stored_password) {
                    return true;
                }
                // 3. ถ้าเก็บเป็นข้อความธรรมดา (ไม่แนะนำ แต่บางระบบอาจใช้)
                else if ($password === $stored_password) {
                    return true;
                }
            }
            return false;
        } catch (PDOException $e) {
            error_log("verifyPassword Error: " . $e->getMessage());
            return false;
        }
    }

    // เมธอดสำหรับอัปเดตรหัสผ่าน
    public function updatePassword($user_id, $new_password) {
        try {
            // ใช้ MD5 เพื่อให้ตรงกับระบบล็อกอิน
            $hashed_password = md5($new_password);
            
            $query = "UPDATE login SET user_pass = :password WHERE user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':user_id', $user_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("updatePassword Error: " . $e->getMessage());
            return false;
        }
    }
}
?>