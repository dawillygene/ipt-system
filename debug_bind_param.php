<?php
// Debug bind_param issue
$type_string = "isssisssssssss";
echo "Type string: '$type_string'\n";
echo "Length: " . strlen($type_string) . "\n";
echo "Characters: ";
for ($i = 0; $i < strlen($type_string); $i++) {
    echo ($i+1) . ":'" . $type_string[$i] . "' ";
}
echo "\n\n";

// Expected parameters:
$params = [
    '1:student_id' => 'i',
    '2:company_name' => 's',
    '3:company_location' => 's',
    '4:position_title' => 's',
    '5:training_duration' => 'i',
    '6:start_date' => 's',
    '7:end_date' => 's',
    '8:training_area' => 's',
    '9:skills_to_acquire' => 's',
    '10:motivation_letter' => 's',
    '11:preferred_company1' => 's',
    '12:preferred_company2' => 's',
    '13:preferred_company3' => 's',
    '14:status' => 's',
    '15:submitted_at' => 's'
];

echo "Expected parameter types:\n";
$expected_string = "";
foreach ($params as $name => $type) {
    echo "$name => $type\n";
    $expected_string .= $type;
}

echo "\nExpected type string: '$expected_string'\n";
echo "Current type string:  '$type_string'\n";
echo "Match: " . ($expected_string === $type_string ? "YES" : "NO") . "\n";

if ($expected_string !== $type_string) {
    echo "\nDifferences:\n";
    for ($i = 0; $i < max(strlen($expected_string), strlen($type_string)); $i++) {
        $e = isset($expected_string[$i]) ? $expected_string[$i] : 'MISSING';
        $c = isset($type_string[$i]) ? $type_string[$i] : 'MISSING';
        if ($e !== $c) {
            echo "Position $i: Expected '$e', Current '$c'\n";
        }
    }
}
?>
