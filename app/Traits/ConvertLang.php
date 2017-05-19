<?php

namespace App\Traits;

trait ConvertLang
{
    /**
     * 重载model的attributesToArray方法，根据当前的语言，转换模型的字段.
     *
     * @return parent->toArray()
     */
    public function attributesToArray()
    {
        $langFields = $this->langFields;

        foreach ($langFields as $field) {
            $this->$field = $this->getLangAttribute($field);
        }

        return parent::attributesToArray();
    }

    /**
     * 重载model的fill方法，将填充语言字段.
     *
     * @param array $attributes
     *
     * @return parent->fill()
     */
    public function fill(array $attributes)
    {
        if ($langFields = $this->langFields) {
            foreach ($langFields as $langField) {
                $enLangField = 'en_' . $langField;

                // 如果同时赋值则不会做处理
                if (array_key_exists($langField, $attributes) && !array_key_exists($enLangField, $attributes)) {
                    $attributes[$enLangField] = $attributes[$langField];
                }
            }
        }

        return parent::fill($attributes);
    }

    public function getLangAttribute($name)
    {
        $lang = \App::getLocale();
        return $lang == 'zh' ? $this->$name : $this->{'en_' . $name};
    }

    public function setLangAttribute($key, $value)
    {
        $lang = \App::getLocale();

        if ($lang == 'zh') {
            $this->$key = $value;
        } else {
            $this->{'en_' . $key} = $value;
        }

        return true;
    }
}
