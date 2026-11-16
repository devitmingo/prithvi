<?php
    if(!function_exists("upload")){
        function upload($file, $path){
            if (!$file) {
                return null;
            }

            // Generate unique file name
            $filename = time() . '_' . rand(1111, 9999) . '.' . $file->getClientOriginalExtension();

            // Store file in storage/app/public/uploads/{path}
            $file->storeAs('public/uploads/' . $path, $filename);

            return $filename;
        }
    }

    if(!function_exists("deleteFile")){
        function deleteFile($filename, $path){
            if (!empty($filename) && \Storage::exists('public/uploads/' . $path . $filename)) {
                \Storage::delete('public/uploads/' . $path . $filename);
            }
        }
    }

    if (!function_exists('formatDateToYmd')) {
        function formatDateToYmd($date){
            if(!$date){
                return null;
            }
            return date('Y-m-d', strtotime($date));
        }
    }

    if (!function_exists('formatDateTodmY')) {
        function formatDateTodmY($date){
            if(!$date){
                return null;
            }
            return date('d-m-Y', strtotime($date));
        }
    }

    if (!function_exists('formatDateTohiA')) {
        function formatDateTohiA($date){
            if(!$date){
                return null;
            }
            return date('h:i A', strtotime($date));
        }
    }
?>