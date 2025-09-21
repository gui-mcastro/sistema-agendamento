<?php

namespace app\models;

use app\components\BaseModel;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "users".
 *
 * @property int            $id
 * @property string         $username
 * @property string         $auth_key
 * @property string         $password_hash
 * @property string|null    $password_reset_token
 * @property string         $nm_nome
 * @property int            $nr_cpf
 * @property string|null    $email
 * @property int            $st_ativo
 * @property string         $created_at
 * @property string|null    $updated_at
 * @property-read mixed     $authKey
 * @property Agendamentos[] $agendamentos
 */
class User extends BaseModel implements IdentityInterface
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
          [
            ['nm_nome', 'email'],
            'required',
            'when' => function ($model) {
                return !$model->cpfExisteNoBanco();
            },
          ],
          [
            [
              'password_reset_token',
              'email',
              'updated_at',
            ],
            'default',
            'value' => null,
          ],
          [
            [
              'nr_cpf',
            ],
            'required',
          ],
          [
            [
              'st_ativo',
            ],
            'integer',
          ],
          [
            [
              'username',
              'password_hash',
              'password_reset_token',
              'email',
              'nr_cpf',

            ],
            'string',
          ],
          [['created_at', 'updated_at'], 'safe'],
          [['auth_key'], 'string', 'max' => 32],
          [['nm_nome'], 'string', 'max' => 500],
          [['username'], 'unique'],
          [['password_reset_token'], 'unique'],
          [['email'], 'unique'],
          ['nr_cpf', 'validateCpf', 'skipOnEmpty' => false],
        ];
    }

    public function validateCpf($attribute)
    {
        $cpf = preg_replace('/[^0-9]/', '', $this->$attribute);

        if (strlen($cpf) !== 11) {
            $this->addError($attribute, '"CPF" deve conter 11 dígitos.');
            return;
        }

        if (preg_match('/^(.)\1*$/', $cpf)) {
            $this->addError($attribute, '"CPF" inválido.');
            return;
        }

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                $this->addError($attribute, '"CPF" inválido.');
                return;
            }
        }
    }

    private function cpfExisteNoBanco()
    {
        return self::find()
          ->where(['nr_cpf' => $this->nr_cpf])
          ->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
          'id' => 'ID',
          'username' => 'Username',
          'auth_key' => 'Auth Key',
          'password_hash' => 'Password Hash',
          'password_reset_token' => 'Password Reset Token',
          'nm_nome' => 'Nome',
          'nr_cpf' => 'CPF',
          'email' => 'Email',
          'st_ativo' => 'St Ativo',
          'created_at' => 'Created At',
          'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Agendamentos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAgendamentos()
    {
        return $this->hasMany(Agendamentos::class, ['cd_user' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        foreach (self::$users as $user) {
            if ($user['accessToken'] === $token) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     *
     * @return static|null
     */
    public static function findByUsername($username)
    {
        foreach (self::$users as $user) {
            if (strcasecmp($user['username'], $username) === 0) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     *
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }

    public function generateUsername($nome_completo)
    {
        $partes = array_filter(explode(' ', trim($nome_completo)));

        if (count($partes) > 0) {
            $primeiro_nome = array_shift($partes);
            $ultimo_nome = !empty($partes) ? end($partes) : '';

            $username = strtolower($primeiro_nome);
            if (!empty($ultimo_nome)) {
                $username .= '.' . strtolower($ultimo_nome);
            }

            return $username;
        }

        return '';
    }

    public function salvarUser()
    {
        $user = self::find()->where(['nr_cpf' => $this->nr_cpf])->one();
        if (!empty($user)) {
            return $user->id;
        }
        $this->username = $this->generateUsername($this->nm_nome);
        $this->auth_key = Yii::$app->security->generateRandomString();
        $this->password_hash = Yii::$app->security->generatePasswordHash('Gui123');
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
        $this->st_ativo = 1;
        $this->created_at = date('Y-m-d H:i:s');
        $this->save();
        return $this->id;
    }

    public function cpfFormatado() {
        $cpf = preg_replace('/[^0-9]/', '', $this->nr_cpf); // Remove tudo que não é número
        return substr($cpf, 0, 3) . '.' .
          substr($cpf, 3, 3) . '.' .
          substr($cpf, 6, 3) . '-' .
          substr($cpf, 9, 2);
    }

}
