#!/bin/bash
# Cleanup script for test files

echo "🧹 Cleaning up test files..."

# Remove test files
files_to_remove=(
    "test_student_reports.php"
    "test_file_upload.php"
    "manual_test.php" 
    "setup_test_session.php"
    "final_verification.php"
)

for file in "${files_to_remove[@]}"; do
    if [ -f "/var/www/html/ipt-system/$file" ]; then
        rm "/var/www/html/ipt-system/$file"
        echo "✅ Removed $file"
    else
        echo "ℹ️  $file not found (already removed)"
    fi
done

# Clean up temporary files
if [ -f "/tmp/test_report_attachment.txt" ]; then
    rm "/tmp/test_report_attachment.txt"
    echo "✅ Removed temporary test file"
fi

echo "🎉 Cleanup completed!"
echo ""
echo "📋 Remaining important files:"
echo "  - RESOLUTION_SUMMARY.md (Documentation)"
echo "  - student_reports.php (Fixed main form)"
echo "  - supervisor/register.php (Fixed registration)"
echo "  - uploads/reports/ (Working upload directory)"
echo ""
echo "💡 The system is now ready for production use!"
