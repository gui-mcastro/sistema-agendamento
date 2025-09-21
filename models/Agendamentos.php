<?php

namespace app\models;

use app\components\BaseModel;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "agendamentos".
 *
 * @property int              $cd_agendamento
 * @property string|null      $dt_agendamento
 * @property int|null         $cd_user
 * @property int|null         $cd_colaborador
 * @property string           $created_at
 * @property string|null      $updated_at
 * @property Colaboradores    $cdColaborador
 * @property-read ActiveQuery $relUser
 * @property-read ActiveQuery $relColaborador
 * @property User             $cdUser
 */
class Agendamentos extends BaseModel
{

    public $hrAgendamento;

    public $hrSearch;

    public $dtSearch;

    public $tpEspecialidade;

    public $nrCpf;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'agendamentos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
          [
            ['dt_agendamento', 'cd_user', 'cd_colaborador', 'updated_at'],
            'default',
            'value' => null,
          ],
          [
            [
              'dt_agendamento',
              'created_at',
              'updated_at',
              'hrAgendamento',
              'tpEspecialidade',
              'nrCpf',
              'hrSearch',
              'dtSearch',
            ],
            'safe',
          ],
          ['cd_colaborador', 'required'],
          [['cd_user', 'cd_colaborador'], 'integer'],
          [
            ['cd_user'],
            'exist',
            'skipOnError' => true,
            'targetClass' => User::class,
            'targetAttribute' => ['cd_user' => 'id'],
          ],
          [
            ['cd_colaborador'],
            'exist',
            'skipOnError' => true,
            'targetClass' => Colaboradores::class,
            'targetAttribute' => ['cd_colaborador' => 'cd_colaborador'],
          ],
          ['dt_agendamento', 'validarDisponibilidade', 'skipOnEmpty' => false],
        ];
    }

    public function validarDisponibilidade($attribute)
    {
        if (empty($this->dt_agendamento)) {
            $this->addError('dt_agendamento', '"Data" não pode ficar em branco.');
            return;
        }
        if (empty($this->hrAgendamento)) {
            $this->addError('hrAgendamento', '"Horário" não pode ficar em branco.');
            return;
        }

        $date = DateTime::createFromFormat('d/m/Y', $this->dt_agendamento)
          ->format('Y-m-d');
        $time = Yii::$app->formatter->asDateTime(
          $this->hrAgendamento,
          'php:H:i:s'
        );
        $ocupado = self::find()->where([
          'DATE(dt_agendamento)' => $date,
          'TIME(dt_agendamento)' => $time,
          'cd_colaborador' => $this->cd_colaborador,
        ])->one();
        if ($ocupado) {
            $this->addError('hrAgendamento', '"Horário" ocupado.');
            return;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
          'cd_agendamento' => 'Cd Agendamento',
          'dt_agendamento' => 'Data',
          'cd_user' => 'Cd User',
          'cd_colaborador' => 'Modalidade',
          'created_at' => 'Created At',
          'updated_at' => 'Updated At',
          'hrAgendamento' => 'Horário',
          'tpEspecialidade' => 'Modalidade',
        ];
    }

    /**
     * Gets query for [[CdColaborador]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRelColaborador()
    {
        return $this->hasOne(Colaboradores::class, ['cd_colaborador' => 'cd_colaborador']);
    }

    /**
     * Gets query for [[CdUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRelUser()
    {
        return $this->hasOne(User::class, ['id' => 'cd_user']);
    }

    public function search(): ActiveDataProvider
    {
        if (!empty($this->dtSearch)) {
            $date = DateTime::createFromFormat('d/m/Y', $this->dtSearch)
              ->format('Y-m-d');
        }
        if (!empty($this->hrSearch)) {
            $time = Yii::$app->formatter->asDateTime(
              $this->hrSearch,
              'php:H:i:s'
            );
        }
        if (!empty($this->nrCpf)) {
            $this->nrCpf = preg_replace('/[^0-9]/', '', $this->nrCpf);
        }
        //        dd($this, $date ?? null, $time ?? null);
        $query = self::find()
          ->alias('a')
          ->joinWith(['relColaborador c', 'relUser u'])
          ->andFilterWhere(['c.tp_especialidade' => $this->tpEspecialidade])
          ->andFilterWhere(['u.nr_cpf' => $this->nrCpf])
          ->andFilterWhere(['DATE(a.dt_agendamento)' => $date ?? null])
          ->andFilterWhere(['TIME(a.dt_agendamento)' => $time ?? null]);

        return new ActiveDataProvider([
          'query' => $query,
          'sort' => [

            'attributes' => [
              'cd_colaborador' => [
                'asc' => ['c.nm_colaborador' => SORT_ASC],
                'desc' => ['c.nm_colaborador' => SORT_DESC],
              ],
              'dt_agendamento',
              'hrAgendamento' => [
                'asc' => ['DATE_FORMAT(a.dt_agendamento, "%H:%i:%s")' => SORT_ASC],
                'desc' => ['DATE_FORMAT(a.dt_agendamento, "%H:%i:%s")' => SORT_DESC],
              ],
              'tpEspecialidade' => [
                'asc' => ['c.tp_especialidade' => SORT_ASC],
                'desc' => ['c.tp_especialidade' => SORT_DESC],
              ],
              'cd_user' => [
                'asc' => ['u.nm_nome' => SORT_ASC],
                'desc' => ['u.nm_nome' => SORT_DESC],
              ],
            ],
          ],
        ]);
    }

    public function salvarTudo($model, $modelUser)
    {
        if (isset($_POST['btn_save'])) {
            $id = $modelUser->salvarUser();
            $model->cd_user = $id;

            // Monta a data/hora do agendamento
            $date = DateTime::createFromFormat('d/m/Y', $model->dt_agendamento)->format('Y-m-d');
            $time = Yii::$app->formatter->asDateTime($model->hrAgendamento, 'php:H:i:s');
            $datetime = $date . ' ' . $time;

            try {
                $connection = Yii::$app->db;
                $transaction = $connection->beginTransaction();

                $result = $connection->createCommand()->insert('agendamentos', [
                  'dt_agendamento' => $datetime,
                  'cd_user' => $model->cd_user,
                  'cd_colaborador' => $model->cd_colaborador,
                  'created_at' => new \yii\db\Expression('NOW()'),
                ])->execute();

                if ($result) {
                    $transaction->commit();
                    return true;
                }

                $transaction->rollBack();
                Yii::error("Falha ao inserir registro", 'agendamento-save');
                return false;
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error([
                  'message' => $e->getMessage(),
                  'trace' => $e->getTraceAsString(),
                ], 'agendamento-save');
                return false;
            }
        }
    }

}
