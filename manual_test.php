<!DOCTYPE html>
<html>
<head>
    <title>Manual Test - Student Reports</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <h1>Manual Test Page for Student Reports</h1>
    
    <h2>Test 1: Form Submission Without File</h2>
    <form method="POST" action="student_reports.php">
        <input type="hidden" name="action" value="submit_report">
        <p><label>Report Type: <select name="report_type"><option value="daily">Daily</option></select></label></p>
        <p><label>Report Title: <input type="text" name="report_title" value="Test Report No File" required></label></p>
        <p><label>Report Content: <textarea name="report_content" required>This is a test report without file attachment.</textarea></label></p>
        <p><label>Report Date: <input type="date" name="report_date" value="<?= date('Y-m-d') ?>" required></label></p>
        <p><label>Activities: <textarea name="activities_completed" required>Testing form submission</textarea></label></p>
        <p><label>Skills: <textarea name="skills_acquired">Form testing skills</textarea></label></p>
        <p><label>Challenges: <textarea name="challenges_faced">None</textarea></label></p>
        <p><input type="submit" name="submit_status" value="submitted" style="background: green; color: white; padding: 10px;"></p>
    </form>
    
    <h2>Test 2: Form Submission With File</h2>
    <form method="POST" action="student_reports.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="submit_report">
        <p><label>Report Type: <select name="report_type"><option value="daily">Daily</option></select></label></p>
        <p><label>Report Title: <input type="text" name="report_title" value="Test Report With File" required></label></p>
        <p><label>Report Content: <textarea name="report_content" required>This is a test report with file attachment.</textarea></label></p>
        <p><label>Report Date: <input type="date" name="report_date" value="<?= date('Y-m-d') ?>" required></label></p>
        <p><label>Activities: <textarea name="activities_completed" required>Testing file upload</textarea></label></p>
        <p><label>Skills: <textarea name="skills_acquired">File upload testing</textarea></label></p>
        <p><label>Challenges: <textarea name="challenges_faced">Ensuring proper file handling</textarea></label></p>
        <p><label>Attachment: <input type="file" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt"></label></p>
        <p><input type="submit" name="submit_status" value="submitted" style="background: blue; color: white; padding: 10px;"></p>
    </form>
    
    <h2>Test 3: Access Main Form</h2>
    <p><a href="student_reports.php" style="background: purple; color: white; padding: 10px; text-decoration: none;">Open Student Reports Form</a></p>
    
    <script>
        // Set up a test student session
        document.addEventListener('DOMContentLoaded', function() {
            // This will help us test the SweetAlert functionality
            if (typeof Swal !== 'undefined') {
                console.log('✓ SweetAlert2 loaded successfully');
                Swal.fire({
                    title: 'Test Ready',
                    text: 'SweetAlert is working correctly',
                    icon: 'success',
                    timer: 2000
                });
            } else {
                console.log('✗ SweetAlert2 not loaded');
            }
        });
    </script>
</body>
</html>
