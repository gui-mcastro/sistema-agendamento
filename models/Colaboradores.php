<?php

namespace app\models;

use app\components\BaseModel;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "colaboradores".
 *
 * @property int $cd_colaborador
 * @property string|null $nm_colaborador
 * @property string|null $tp_especialidade
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Agendamentos[] $agendamentos
 */
class Colaboradores extends BaseModel
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'colaboradores';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nm_colaborador', 'tp_especialidade', 'updated_at'], 'default', 'value' => null],
            [['created_at'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['nm_colaborador'], 'string', 'max' => 255],
            [['tp_especialidade'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'cd_colaborador' => 'Medico',
            'nm_colaborador' => 'Nm Colaborador',
            'tp_especialidade' => 'Tp Especialidade',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Agendamentos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRelAgendamentos()
    {
        return $this->hasMany(Agendamentos::class, ['cd_colaborador' => 'cd_colaborador']);
    }

}
