<?php

namespace app\controllers;

use app\models\Agendamentos;
use app\models\User;
use DateTime;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\widgets\ActiveForm;

class SiteController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
          'access' => [
            'class' => AccessControl::class,
            'only' => ['logout'],
            'rules' => [
              [
                'actions' => ['logout'],
                'allow' => true,
                'roles' => ['@'],
              ],
            ],
          ],
          'verbs' => [
            'class' => VerbFilter::class,
            'actions' => [
              'logout' => ['post'],
              'agendamentos' => ['post', 'get'],
              'deletar' => ['post', 'get'],
            ],
          ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
          'error' => [
            'class' => 'yii\web\ErrorAction',
          ],
          'captcha' => [
            'class' => 'yii\captcha\CaptchaAction',
            'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
          ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
          'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
          'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionAgendamentos()
    {
        Yii::$app->cache->flush();
        $model = new Agendamentos();
        $modelUser = new User();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model->load(Yii::$app->request->post());
            $modelUser->load(Yii::$app->request->post());
            $erros = array_merge(ActiveForm::validate($model), ActiveForm::validate($modelUser));
            if (!empty($erros)) {
                return $erros;
            }

            if ($model->salvarTudo($model, $modelUser)) {
                Yii::$app->session->setFlash('success', 'Agendamento realizado com sucesso.');
                return $this->redirect(['agendamentos']);
            }
        }
        $model->load(Yii::$app->request->queryParams);
        //        dd($model, Yii::$app->request->queryParams);
        $dataProvider = $model->search();
        return $this->render('agendamentos/index', [
          'model' => $model,
          'modelUser' => $modelUser,
          'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCarregaCampos()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $modelUser = (new User())->find()->where(['nr_cpf' => Yii::$app->request->post('cpf')])->one();
        if (!$modelUser) {
            return [];
        }
        return $modelUser->attributes;
    }

    public function actionDeletar($id)
    {
        $model = Agendamentos::find()->where(['cd_agendamento' => $id])->one();

        try {
            if ($model->delete()) {
                Yii::$app->session->setFlash('success', 'Registro excluído com sucesso.');
            } else {
                Yii::$app->session->setFlash('error', 'Não foi possível excluir o registro.');
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Erro ao excluir o registro.');
        }

        return $this->redirect(['agendamentos']);
    }

}
