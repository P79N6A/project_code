<?php
namespace app\models\news;

use yii\base\Model;
use yii\web\UploadedFile;

class Upload extends Model {
    public $excel_file;
    public $file_path;

    public function rules() {
        return [
            [['excel_file'], 'file'],
        ];
    }

    public function upload() {
        if ($this->validate()) {
            $dir = "upload/" . date("Ymd");
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $fileName = date("HiiHsHis") . $this->excel_file->baseName . "." . $this->excel_file->extension;
            $dir = $dir . "/" . $fileName;
            $this->excel_file->saveAs($dir);
            $this->file_path = "upload/" . date("Ymd") . "/" . $fileName;
            return true;
        } else {
            return false;
        }
    }
}