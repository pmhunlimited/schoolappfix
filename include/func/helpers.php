<?php
if (!function_exists('is_feature_active')) {
    /**
     * Checks if a specific feature is active for a given school.
     *
     * @param string $school_id_number The ID of the school.
     * @param string $feature_name The name of the feature (e.g., 'bulk_print').
     * @param mysqli $connection The database connection object.
     * @return bool True if the feature is active, false otherwise.
     */
    function is_feature_active($school_id_number, $feature_name, $connection) {
        $school_id_number_safe = mysqli_real_escape_string($connection, $school_id_number);
        $feature_name_safe = mysqli_real_escape_string($connection, $feature_name);

        $query = "SELECT activation_status FROM sm_feature_activations WHERE school_id_number='$school_id_number_safe' AND feature_name='$feature_name_safe'";
        $result = mysqli_query($connection, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['activation_status'] === 'active';
        }

        return false;
    }
}

if (!function_exists('studentClassName')) {
    function studentClassName($class_info, $school_id) {
        global $connection_server;
        $class_name = "N/A";
        $get_class_name = mysqli_query($connection_server, "SELECT class_name FROM sm_classes WHERE school_id_number='$school_id' AND numeric_class_name='$class_info' LIMIT 1");
        if ($class_name_array = mysqli_fetch_array($get_class_name)) {
            $class_name = $class_name_array["class_name"];
        }
        return $class_name;
    }
}

if (!function_exists('subjectName')) {
    function subjectName($subject_info, $school_id) {
        global $connection_server;
        $subject_name = "N/A";
        $get_subject_name = mysqli_query($connection_server, "SELECT subject_name FROM sm_subjects WHERE school_id_number='$school_id' AND subject_code='$subject_info' LIMIT 1");
        if ($subject_name_array = mysqli_fetch_array($get_subject_name)) {
            $subject_name = $subject_name_array["subject_name"];
        }
        return $subject_name;
    }
}

if (!function_exists('termName')) {
    function termName($terms_info, $school_id) {
        global $connection_server;
        $term_name = "N/A";
        $get_term_name = mysqli_query($connection_server, "SELECT term_name FROM sm_terms WHERE school_id_number='$school_id' AND id_number='$terms_info' LIMIT 1");
        if($term_name_array = mysqli_fetch_array($get_term_name)){
            $term_name = $term_name_array["term_name"];
        }
        return $term_name;
    }
}

if (!function_exists('getScoreGrade')) {
    function getScoreGrade($score, $return_type, $school_id) {
        global $connection_server;
        $get_grade_details = mysqli_query($connection_server, "SELECT * FROM sm_grades WHERE school_id_number='$school_id' AND ($score >= mark_from AND $score <= mark_to)");
        if(mysqli_num_rows($get_grade_details) > 0){
            $grade_details = mysqli_fetch_array($get_grade_details);
            if($return_type == "grade"){
                return $grade_details["name"];
            } else {
                return $grade_details["remark"];
            }
        } else {
            return "N/A";
        }
    }
}

if (!function_exists('principalRemark')) {
    function principalRemark($gender, $average) {
        $pronoun_1 = ($gender == "male") ? "He" : "She";
        if($average >= 70){
            return "$pronoun_1 has an outstanding performance, keep it up.";
        } elseif($average >= 60){
            return "$pronoun_1 has a very good performance.";
        } elseif($average >= 50){
            return "A good performance, but $pronoun_1 can do better.";
        } elseif($average >= 40){
            return "$pronoun_1 has a fair performance and can be improved.";
        } else {
            return "A poor performance, $pronoun_1 needs to sit up and do better.";
        }
    }
}
