<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'process') {
    try {
        $database = new Database();
        $db = $database->connect();

        // เริ่ม transaction
        $db->beginTransaction();

        // รับค่าจากฟอร์ม
        $com_id = $_POST['com_id'];
        $stu_id = $_POST['stu_id'];
        $activity_name = $_POST['activity_name'];
        $activity_hour = $_POST['activity_hour'];
        $semester = $_POST['semester'];
        $year = $_POST['year'];
        $status = $_POST['status'];
        $act_transfer = isset($_POST['act_transfer']) ? $_POST['act_transfer'] : null;

        if ($status === 'approved' && !empty($act_transfer)) {
            // ดึงข้อมูลกิจกรรมเทียบโอนที่เลือก
            $sql = "SELECT * FROM act_transfer WHERE Acttrans_id = :act_transfer";
            $stmt = $db->prepare($sql);
            $stmt->execute([':act_transfer' => $act_transfer]);
            $transfer_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($transfer_data) {
                // ใช้ข้อมูลจาก act_transfer
                $activity_name = $transfer_data['Acttrans_name'];
                $activity_hour = $transfer_data['Acttrans_hour'];
                $act_type = $transfer_data['ActType_id'];
            }
        }

        // สร้างรหัสใหม่สำหรับ transfer
        $sql = "SELECT COALESCE(MAX(CAST(Com_id AS UNSIGNED)), 0) + 1 AS next_id FROM transfer";
        $stmt = $db->query($sql);
        $next_id = $stmt->fetch(PDO::FETCH_ASSOC)['next_id'];
        $new_com_id = str_pad($next_id, 5, '0', STR_PAD_LEFT);

        if ($status === 'approved') {
            // บันทึกข้อมูลลงตาราง transfer
            $sql = "INSERT INTO transfer (Com_id, Com_name, Com_hour, Com_amount, Com_semester, Com_year, Com_status, Stu_id) 
                    VALUES (:com_id, :activity_name, :activity_hour, 1, :semester, :year, :status, :stu_id)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':com_id' => $new_com_id,
                ':activity_name' => $activity_name,
                ':activity_hour' => $activity_hour,
                ':semester' => $semester,
                ':year' => date('Y-m-d', strtotime($year . '-01-01')),
                ':status' => $status,
                ':stu_id' => $stu_id
            ]);
        }

        // ลบข้อมูลจากตาราง comparision
        $sql = "DELETE FROM comparision WHERE Com_id = :com_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':com_id' => $com_id]);

        // ยืนยัน transaction
        $db->commit();

        $_SESSION['success'] = "บันทึกผลการพิจารณาเรียบร้อยแล้ว";
    } catch(PDOException $e) {
        // ถ้าเกิดข้อผิดพลาด ให้ rollback การทำงานทั้งหมด
        $db->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }

    header("Location: ../index.php?menu=11");
    exit();
}
?>