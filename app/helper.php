<?php
    if(!function_exists("upload")){
        function upload($file, $path){
            if (!$file) {
                return null;
            }

            // Generate unique file name
            $filename = time() . '_' . rand(1111, 9999) . '.' . $file->getClientOriginalExtension();

            // Store file in default public storage disk
            $file->storeAs('public/uploads/' . $path, $filename);

            // Also mirror directly into public/storage/public/uploads if public/storage is a real directory
            try {
                $publicTargetDir = public_path('storage/public/uploads/' . $path);
                if (!is_link(public_path('storage')) && is_dir(public_path('storage'))) {
                    if (!file_exists($publicTargetDir)) {
                        @mkdir($publicTargetDir, 0755, true);
                    }
                    $source = storage_path('app/public/public/uploads/' . $path . $filename);
                    if (file_exists($source)) {
                        @copy($source, $publicTargetDir . $filename);
                    }
                }
            } catch (\Throwable $e) {
                // Silently continue if filesystem permissions disallow
            }

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