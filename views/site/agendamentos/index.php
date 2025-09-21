<?php

use app\models\Agendamentos;
use app\models\Colaboradores;
use kartik\date\DatePicker;
use kartik\time\TimePicker;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\JqueryAsset;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;
use yii\data\ActiveDataProvider;

/**
 * @var $model        Agendamentos
 * @var $dataProvider ActiveDataProvider
 */
JqueryAsset::register($this);
$form = ActiveForm::begin([
  'id' => 'agendamentos-search',
  'method' => 'get',
]);

?>
<div class="card">
    <div class="card-header">
        <h4>Consultar Agendamentos</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-3">
                <?=
                $form->field($model, 'nrCpf')->widget(
                  MaskedInput::class,
                  [
                    'mask' => ['999.999.999-99'],
                    'clientOptions' => [
                      'readOnly' => true,
                      'keepStatic' => true,
                      'greedy' => false,
                      'clearIncomplete' => false,
                      'autoUnmask' => true,
                      'removeMaskOnSubmit' => true,
                    ],
                    'options' => [
                      'placeholder' => 'Digite o CPF...',
                      'class' => 'form-control',
                    ],
                  ]
                )->label('CPF'); ?>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <?= $form->field($model, 'tpEspecialidade')->dropDownList(
                      Colaboradores::dropDownList(
                        ['tp_especialidade', 'tp_especialidade'],
                        sort: 'tp_especialidade'
                      ),
                      [
                        'prompt' => 'Selecione a modalidade...',
                        'class' => 'form-select',
                      ]
                    ) ?>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <?= $form->field($model, 'dtSearch')
                      ->widget(MaskedInput::class, [
                        'mask' => '99/99/9999',
                        'options' => ['placeholder' => 'dd/mm/aaaa',],

                      ])->widget(DatePicker::class, [
                        'language' => 'pt',
                        'type' => DatePicker::TYPE_COMPONENT_APPEND,
                        'pluginOptions' => [
                          'autoclose' => true,
                          'format' => 'dd/mm/yyyy',
                          'orientation' => 'bottom',
                        ],
                        'options' => ['placeholder' => 'dd/mm/aaaa'],
                      ]) ?>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <?= $form->field($model, 'hrSearch')->widget(TimePicker::class, [
                      'pluginOptions' => [
                        'showSeconds' => false,
                        'showMeridian' => false,
                        'minuteStep' => 30,
                        'minTime' => '08:00',
                        'maxTime' => '18:00',
                        'defaultTime' => false,

                      ],
                      'options' => ['placeholder' => 'HH:mm', 'value' => $model->hrSearch ?: '',],
                    ])
                    ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <?php
                echo Html::submitButton('<i class="fas fa-search"></i> Pesquisar', [
                  'name' => 'btn-search',
                  'class' => 'btn btn-primary',
                  'encode' => false,
                  'style' => 'margin-right: 10px;',
                ]);
                echo Html::button('<i class="fas fa-plus"></i> Agendar Horário', [
                  'class' => 'btn btn-primary',
                  'data-bs-toggle' => 'modal',
                  'data-bs-target' => '#agendamento-modal',
                ]);
                ?>

            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h4>Resultados</h4>
    </div>
    <div class="card-body">
        <?=
        GridView::widget([
          'dataProvider' => $dataProvider,
          'columns' => [
            [
              'attribute' => 'cd_user',
              'value' => fn($model) => $model->relUser->cpfFormatado() . ' - ' . $model->relUser->nm_nome,
              'label' => 'Paciente',
            ],
            [
              'attribute' => 'tpEspecialidade',
              'value' => 'relColaborador.tp_especialidade',
            ],
            [
              'attribute' => 'cd_colaborador',
              'value' => 'relColaborador.nm_colaborador',
              'label' => 'Médico',
            ],
            [
              'attribute' => 'dt_agendamento',

              'value' => fn($model) => Yii::$app->formatter->asDate($model->dt_agendamento, 'php:d/m/Y'),
            ],
            [
              'attribute' => 'hrAgendamento',
              'value' => fn($model) => Yii::$app->formatter->asDate($model->dt_agendamento, 'php:H:i'),
            ],
            [
              'class' => ActionColumn::class,
              'template' => '{delete}',
              'buttons' => [
                'delete' => function ($url, $model, $key) {
                    return Html::a(
                      '<i class="fas fa-trash"></i>',
                      ['deletar', 'id' => $model->cd_agendamento],
                      [
                        'class' => 'btn btn-danger btn-sm',
                        'data' => [
                          'confirm' => 'Tem certeza que deseja excluir este item?',
                          'method' => 'post',
                          'params' => [
                            Yii::$app->request->csrfParam => Yii::$app->request->csrfToken
                          ],

                        ],
                      ]
                    );
                },
              ],
            ],
          ],
        ])

        ?>
    </div>
</div>
<?php

ActiveForm::end();

echo $this->render('modal', [
  'model' => new Agendamentos(),
  'modelUser' => new \app\models\User(),
]);
$this->registerJs(
  <<<JS
    $(function() {
        const myModal = new bootstrap.Modal($('#agendamento-modal'), {
            backdrop: true,
            keyboard: true,
        });
        
        $('#btn-save-agendamento').on('click', function (e) {
            e.preventDefault();
            console.log('save');
            const extraParam = { btn_save: '' };
            $.post($('#agendamento-form').attr('action'), $('#agendamento-form').serialize() + '&' + $.param(extraParam))
              .done(function (data) {
                  if (data == true) {
                      location.reload();
                  } else {
                      $('.form-control, .form-select').removeClass('is-invalid');
                      $('.invalid-feedback').remove();
                      $.each(data, function (key, value) {
                          $('#' + key)
                            .addClass('is-invalid')
                            .parent()
                            .append(`<div class="invalid-feedback">`+ value[0] + `</div>`)
                            .show();
                      });
                  }
              });
        })

        $('#agendamento-form').on('afterValidate', function (e) {
            $('.invalid-feedback').show();
        });
        
    });
JS,
  View::POS_READY
);
?>

