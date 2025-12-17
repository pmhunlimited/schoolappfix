<?php
// include/func/helpers.php

if (!function_exists('studentClassName')) {
    function studentClassName($class_info, $school_id) {
        global $connection_server;
        $class_name = '';
        $get_class_name = mysqli_query($connection_server, "SELECT class_name FROM sm_classes WHERE school_id_number='$school_id' AND numeric_class_name='$class_info' GROUP BY numeric_class_name");
        if (mysqli_num_rows($get_class_name) > 0) {
            $class_name_array = mysqli_fetch_array($get_class_name);
            $class_name = $class_name_array["class_name"];
        } else {
            $class_name = "N/A";
        }
        return $class_name;
    }
}

if (!function_exists('subjectName')) {
    function subjectName($subject_info, $school_id) {
        global $connection_server;
        $subject_name = '';
        $get_subject_name = mysqli_query($connection_server, "SELECT subject_name FROM sm_subjects WHERE school_id_number='$school_id' AND subject_code='$subject_info'");
        if (mysqli_num_rows($get_subject_name) > 0) {
            $subject_id_array = mysqli_fetch_array($get_subject_name);
            $subject_name = $subject_id_array["subject_name"];
        } else {
            $subject_name = "N/A";
        }
        return $subject_name;
    }
}

if (!function_exists('termName')) {
    function termName($terms_info, $school_id) {
        global $connection_server;
        $term_name = '';
        $get_term_name = mysqli_query($connection_server, "SELECT term_name FROM sm_terms WHERE school_id_number='$school_id' AND id_number='$terms_info'");
        if (mysqli_num_rows($get_term_name) == 1) {
            $term_name_array = mysqli_fetch_array($get_term_name);
            $term_name = $term_name_array["term_name"];
        } else {
            $term_name = "N/A";
        }
        return $term_name;
    }
}

if (!function_exists('getScoreGrade')) {
    function getScoreGrade($score_info, $type_info, $school_id) {
        global $connection_server;
        $grade_name = '';
        $get_grade_name = mysqli_query($connection_server, "SELECT * FROM sm_grades WHERE school_id_number='$school_id'");
        if (mysqli_num_rows($get_grade_name) > 0) {
            while ($grade_name_array = mysqli_fetch_array($get_grade_name)) {
                if ($score_info >= $grade_name_array["mark_from"] && $score_info <= $grade_name_array["mark_upto"]) {
                    if ($type_info == "grade") {
                        $grade_name = $grade_name_array["grade_name"];
                    }
                    if ($type_info == "remark") {
                        $grade_name = $grade_name_array["grade_comment"];
                    }
                }
            }
        } else {
            $grade_name = "N/A";
        }
        return $grade_name;
    }
}

if (!function_exists('principalRemark')) {
    function principalRemark($gender, $average_score) {
        $average_score = floor($average_score);
        if (strtolower($gender) == "male") {
            $gender_pronoun_1 = "He ";
            $gender_pronoun_2 = "His ";
        } elseif (strtolower($gender) == "female") {
            $gender_pronoun_1 = "She ";
            $gender_pronoun_2 = "Her ";
        } else {
            $gender_pronoun_1 = "He/She ";
            $gender_pronoun_2 = "His/Her ";
        }
        if ($average_score >= 70) return $gender_pronoun_1 . " performs independent work with confidence and focus.";
        if ($average_score >= 60) return $gender_pronoun_1 . " is focused during classroom activities and willingly participated in class discussions.";
        if ($average_score >= 50) return $gender_pronoun_1 . " is an active participant in class.";
        if ($average_score >= 45) return $gender_pronoun_1 . " needs frequent reminders to be attentive during class";
        if ($average_score >= 40) return $gender_pronoun_1 . " needs to improve on " . strtolower($gender_pronoun_2) . " performance.";
        if ($average_score < 40) return $gender_pronoun_2 . " result is not impressive, " . strtolower($gender_pronoun_1) . " needs to improve.";
        return "";
    }
}
?>
