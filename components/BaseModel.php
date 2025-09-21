<?php

namespace app\components;

use yii\db\ActiveRecord;

class BaseModel extends ActiveRecord
{
    public static function dropDownList($fields, $where = null, $sort = null)
    {
        $key = isset($fields[0]) ? $fields[0] : null;
        $value = isset($fields[1]) ? $fields[1] : null;

        $items = [];
        if ($key && $value) {
            $query = self::find()
              ->select(['valueDropDownList' => $value])
              ->where($where)
              ->indexBy($key);
            if (!empty($sort)) {
                $query->orderBy($sort);
            }

            $items = $query->column();
        }

        return $items;
    }
}
