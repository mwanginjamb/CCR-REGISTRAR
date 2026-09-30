<?php
/**
 * Patient index view — DataTables + Tailwind MD3 design
 *
 * @var yii\web\View            $this
 * @var app\models\PatientSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 */

use app\assets\PatientAsset;
use app\library\FormUi;
use app\models\Patient;
use yii\helpers\Html;
use yii\helpers\Url;

PatientAsset::register($this);
$this->title = 'Patients';

/*
 * Disable Yii2 pagination so every record is rendered into the <table>.
 * DataTables then owns client-side paging, searching, and sorting.
 *
 * ⚠ Switch to server-side processing (actionPatientData) for > 5 000 rows —
 *   see the controller note in your previous session.
 */
$dataProvider->pagination = false;
$models = $dataProvider->getModels();
$totalCount = count($models);

// Decode maps — resolved once, used in both desktop and mobile loops
$ethnicGroups = Patient::getEthnicGroups();
$religions = Patient::getReligions();

/*
 * Badge variant per ethnic_group integer key.
 * Extend the map as new groups are added to Patient::getEthnicGroups().
 */
$ethnicVariant = [
  1 => 'secondary',   // African   — blue-teal
  2 => 'tertiary',    // Asian     — warm sand
  3 => 'default',     // Caucasian — neutral
  4 => 'warning',     // Hispanic  — amber
  5 => 'default',     // Other     — neutral
];

$religionVariant = [
  1 => 'secondary',   // Christian
  2 => 'tertiary',    // Muslim
  3 => 'default',     // Other
];

// Export title baked into the JS via PHP string interpolation
$exportTitle = Html::encode(Yii::$app->name . ' — Patient Registry');
?>

<?php /* ─── Page header ─────────────────────────────────────────────────── */ ?>
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-8 md:mb-12">
  <div>
    <?= FormUi::breadcrumb(['Database', 'Patients']) ?>
    <h2 class="text-3xl md:text-4xl font-headline font-extrabold text-primary tracking-tight">
      Patient Registry
    </h2>
    <p class="text-on-surface-variant mt-2 text-base md:text-lg">
      Centralised patient records — search, filter, and export.
    </p>
  </div>
  <?= FormUi::ctaButton('New Patient', 'person_add', ['create']) ?>
</div>

<?php /* ─── Stat chips ──────────────────────────────────────────────────── */ ?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
  <?= FormUi::statChip('group', 'Total Patients', number_format($totalCount)) ?>
  <?= FormUi::statChip(
    'pending_actions',
    'Pending Review',
    '—',
    'bg-tertiary-fixed',
    'text-on-tertiary-fixed-variant'
  ) ?>
</div>

<?php /* ─── Grid container ──────────────────────────────────────────────── */ ?>
<div class="<?= FormUi::gridContainerClass() ?>">

  <?php /* ── Toolbar: export slot (left) + search (right) ──────────────── */ ?>
  <div class="<?= FormUi::gridToolbarClass() ?>">

    <?php /* DataTables moves the Buttons widget here after init */ ?>
    <div id="dt-export-slot" class="flex items-center gap-2 flex-wrap order-2 sm:order-1"></div>

    <div class="relative w-full sm:w-72 order-1 sm:order-2">
      <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">
        search
      </span>
      <input id="dt-custom-search" type="text" placeholder="Search patients…"
        class="<?= FormUi::gridSearchClass() ?>" />
    </div>
  </div>

  <?php /* ── Desktop table (hidden below 1024 px via CSS) ───────────────── */ ?>
  <div id="desktop-table-wrap" class="overflow-x-auto">
    <table id="patientsTable" class="w-full text-left border-collapse">

      <thead>
        <tr class="bg-surface-container-high">
          <th class="<?= FormUi::thClass() ?>">Patient ID</th>
          <th class="<?= FormUi::thClass() ?>">Full Name</th>
          <th class="<?= FormUi::thClass() ?>">National ID</th>
          <th class="<?= FormUi::thClass() ?>">Age / DOB</th>
          <th class="<?= FormUi::thClass() ?>">Telephone</th>
          <th class="<?= FormUi::thClass() ?>">Ethnic Group</th>
          <th class="<?= FormUi::thClass() ?>">Place of Birth</th>
          <th class="<?= FormUi::thClass() ?>">Registered</th>
          <th class="<?= FormUi::thClass(true) ?>" data-dt-orderable="false">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-surface-variant/30">
        <?php foreach ($models as $model): ?>

          <?php
          // Resolve display values once per row
          $ethnicLabel = $ethnicGroups[$model->ethnic_group] ?? '—';
          $ethnicVar = $ethnicVariant[$model->ethnic_group] ?? 'default';
          $dob = $model->date_of_birth
            ? Yii::$app->formatter->asDate($model->date_of_birth, 'd M Y')
            : '—';
          $registered = $model->created_at
            ? Yii::$app->formatter->asDate($model->created_at, 'd M Y')
            : '—';
          $patientId = sprintf('PT-%05d', $model->id);
          ?>

          <tr class="<?= FormUi::trClass() ?>">

            <?php /* Patient ID */ ?>
            <td class="<?= FormUi::tdClass('primary') ?>"><?= Html::encode($patientId) ?></td>

            <?php /* Full Name + National ID as subtext */ ?>
            <td class="<?= FormUi::tdClass() ?>">
              <div class="flex flex-col">
                <span class="text-on-surface font-semibold text-sm">
                  <?= Html::encode($model->full_name ?? '—') ?>
                </span>
                <span class="text-xs text-outline">
                  ID: <?= Html::encode($model->id) ?>
                </span>
              </div>
            </td>

            <?php /* National ID */ ?>
            <td class="<?= FormUi::tdClass('muted') ?>">
              <?= Html::encode($model->national_id ?? '—') ?>
            </td>

            <?php /* Age / Date of Birth */ ?>
            <td class="<?= FormUi::tdClass() ?>">
              <div class="flex flex-col">
                <?php if ($model->age): ?>
                  <span class="text-on-surface font-semibold text-sm">
                    <?= Html::encode($model->age) ?> yrs
                  </span>
                <?php endif ?>
                <span class="text-xs text-outline"><?= Html::encode($dob) ?></span>
              </div>
            </td>

            <?php /* Telephone */ ?>
            <td class="<?= FormUi::tdClass('muted') ?>">
              <?= Html::encode($model->telephone_no_patient ?? '—') ?>
            </td>

            <?php /* Ethnic Group badge */ ?>
            <td class="<?= FormUi::tdClass() ?>">
              <?= FormUi::badge($ethnicLabel, $ethnicVar) ?>
            </td>

            <?php /* Place of Birth chip */ ?>
            <td class="<?= FormUi::tdClass() ?>">
              <?php if ($model->place_of_birth): ?>
                <?= FormUi::chip($model->place_of_birth) ?>
              <?php else: ?>
                <span class="text-outline text-sm">—</span>
              <?php endif ?>
            </td>

            <?php /* Registered date */ ?>
            <td class="<?= FormUi::tdClass('muted') ?>"><?= Html::encode($registered) ?></td>

            <?php /* Row actions */ ?>
            <td class="<?= FormUi::tdClass() ?> text-right">
              <div class="flex items-center justify-end gap-1">
                <?= FormUi::actionBtn('visibility', ['view', 'id' => $model->id], 'view', ['title' => 'View']) ?>
                <?= FormUi::actionBtn('edit_square', ['update', 'id' => $model->id], 'edit', ['title' => 'Edit']) ?>
                <?= FormUi::actionBtn('delete', ['delete', 'id' => $model->id], 'delete', ['title' => 'Delete']) ?>
              </div>
            </td>

          </tr>
        <?php endforeach ?>
      </tbody>

    </table>
  </div><?php /* /desktop-table-wrap */ ?>


  <?php /* ── Mobile card list (shown below 1024 px via CSS) ───────────── */ ?>
  <div id="mobile-cards" class="flex-col divide-y divide-surface-variant/30">

    <?php foreach ($models as $model): ?>

      <?php
      $ethnicLabel = $ethnicGroups[$model->ethnic_group] ?? '—';
      $ethnicVar = $ethnicVariant[$model->ethnic_group] ?? 'default';
      $registered = $model->created_at
        ? Yii::$app->formatter->asDate($model->created_at, 'd M Y')
        : '—';
      $patientId = sprintf('PT-%05d', $model->id);
      ?>

      <div class="p-4 bg-surface-container-lowest">

        <div class="flex justify-between items-start mb-3">
          <div>
            <p class="text-[10px] font-bold text-outline-variant uppercase tracking-widest mb-1">
              <?= Html::encode($patientId) ?>
            </p>
            <h4 class="font-bold text-primary text-sm">
              <?= Html::encode($model->full_name ?? '—') ?>
            </h4>
            <p class="text-xs text-outline">
              NID: <?= Html::encode($model->national_id ?? '—') ?>
            </p>
          </div>
          <?= FormUi::badge($ethnicLabel, $ethnicVar) ?>
        </div>

        <div class="flex flex-wrap gap-2 mb-3">
          <?php if ($model->telephone_no_patient): ?>
            <?= FormUi::chip($model->telephone_no_patient) ?>
          <?php endif ?>
          <?php if ($model->place_of_birth): ?>
            <?= FormUi::chip($model->place_of_birth, 'secondary') ?>
          <?php endif ?>
          <?php if ($model->age): ?>
            <?= FormUi::chip($model->age . ' yrs', 'default') ?>
          <?php endif ?>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-xs text-on-surface-variant">
            Registered: <?= Html::encode($registered) ?>
          </span>
          <div class="flex gap-1">
            <?= FormUi::actionBtnSm('visibility', ['view', 'id' => $model->id], 'view') ?>
            <?= FormUi::actionBtnSm('edit_square', ['update', 'id' => $model->id], 'edit') ?>
            <?= FormUi::actionBtnSm('delete', ['delete', 'id' => $model->id], 'delete') ?>
          </div>
        </div>

      </div>

    <?php endforeach ?>

  </div><?php /* /mobile-cards */ ?>

</div><?php /* /grid container */ ?>


<?php
$style = <<<CSS
/* ── Kill DataTables default borders / wrapper backgrounds ── */
table.dataTable thead th,
table.dataTable thead td { 
    border-bottom: none !important; 
    background: transparent !important;
}

table.dataTable.no-footer { 
    border-bottom: none !important; 
}

div.dt-container { 
    font-family: 'Inter', sans-serif; 
}

/* ── Wrapper spacing ── */
div.dt-container {
    padding: 0 !important;
}

/* ── Search input styling ── */
div.dt-search {
    margin-bottom: 1rem !important;
}

div.dt-search label {
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    color: #6b7280 !important;
    font-family: 'Inter', sans-serif !important;
}

div.dt-search input {
    padding: 0.5rem 1rem !important;
    border-radius: 0.5rem !important;
    border: 1px solid #e2e8f0 !important;
    font-size: 0.875rem !important;
    color: #1f2937 !important;
    font-family: 'Inter', sans-serif !important;
    background: #ffffff !important;
    transition: all 0.2s ease !important;
    width: auto !important;
    min-width: 200px !important;
}

div.dt-search input:focus {
    outline: none !important;
    border-color: #2563eb !important;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2) !important;
}

div.dt-search input::placeholder {
    color: #9ca3af !important;
}

/* ── Length selector / info ── */
div.dt-length {
    margin-bottom: 1rem !important;
}

div.dt-length label {
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    color: #6b7280 !important;
    font-family: 'Inter', sans-serif !important;
}

div.dt-length select {
    padding: 0.5rem 2.25rem 0.5rem 1rem !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 0.5rem !important;
    background: #ffffff !important;
    font-size: 0.875rem !important;
    color: #1f2937 !important;
    font-family: 'Inter', sans-serif !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    appearance: auto !important;
    -webkit-appearance: auto !important;
}

div.dt-length select:focus {
    outline: none !important;
    border-color: #2563eb !important;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2) !important;
}

div.dt-info {
    padding-top: 1rem !important;
    font-size: 0.75rem !important;
    font-weight: 500 !important;
    color: #6b7280 !important;
    font-family: 'Inter', sans-serif !important;
}

/* ── Pagination buttons ── */
div.dt-paging {
    padding-top: 1rem !important;
}

div.dt-paging .dt-paging-button {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 2.25rem !important;
    min-width: 2.25rem !important;
    padding: 0 0.75rem !important;
    margin: 0 0.125rem !important;
    border-radius: 0.5rem !important;
    border: none !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    font-family: 'Inter', sans-serif !important;
    cursor: pointer !important;
    background: transparent !important;
    color: #6b7280 !important;
    transition: all 0.2s ease !important;
}

div.dt-paging .dt-paging-button:hover:not(.disabled):not(.current) {
    background: #f3f4f6 !important;
    color: #1f2937 !important;
}

div.dt-paging .dt-paging-button.current,
div.dt-paging .dt-paging-button.current:hover {
    background: #2563eb !important;
    color: #ffffff !important;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
}

div.dt-paging .dt-paging-button.disabled {
    opacity: 0.4 !important;
    cursor: not-allowed !important;
}

div.dt-paging .dt-paging-button.disabled:hover {
    background: transparent !important;
    color: #6b7280 !important;
}

/* ── Sortable header arrows ── */
table.dataTable thead th.dt-orderable-asc,
table.dataTable thead th.dt-orderable-desc {
    cursor: pointer !important;
    position: relative !important;
}

table.dataTable thead th.dt-orderable-asc:hover,
table.dataTable thead th.dt-orderable-desc:hover {
    background: rgba(37, 99, 235, 0.05) !important;
}

/* ── DataTable container background ── */
div.dt-container {
    background: transparent !important;
}

/* ── Table row hover ── */
table.dataTable tbody tr:hover {
    background: rgba(0, 0, 0, 0.02) !important;
}

/* ── Responsive breakpoints ── */
@media (max-width: 1024px) {
    #desktop-table-wrap { 
        display: none !important; 
    }
    #mobile-cards { 
        display: flex !important; 
    }
}

@media (min-width: 1025px) {
    #mobile-cards { 
        display: none !important; 
    }
}

/* ── Mobile responsive for DataTables controls ── */
@media (max-width: 640px) {
    div.dt-search {
        float: none !important;
        text-align: center !important;
        width: 100% !important;
    }
    
    div.dt-search label {
        flex-direction: column !important;
        width: 100% !important;
    }
    
    div.dt-search input {
        width: 100% !important;
        min-width: unset !important;
    }
    
    div.dt-length {
        float: none !important;
        text-align: center !important;
        width: 100% !important;
    }
    
    div.dt-length label {
        justify-content: center !important;
    }
    
    div.dt-info {
        float: none !important;
        text-align: center !important;
        width: 100% !important;
    }
    
    div.dt-paging {
        float: none !important;
        text-align: center !important;
        width: 100% !important;
    }
}

/* ── Zebra striping (optional) ── */
table.dataTable tbody tr:nth-child(even) {
    background: rgba(0, 0, 0, 0.01) !important;
}

/* ── Custom scroll for table wrapper ── */
div.dt-scroll {
    overflow-x: auto !important;
}

/* ── Ensure table header stays sticky ── */
table.dataTable thead th {
    position: sticky !important;
    top: 0 !important;
    z-index: 10 !important;
}
CSS;
$this->registerCss($style);
?>


<?php /* ─── DataTables initialisation ───────────────────────────────────── */ ?>
<?php
$js = <<<JS
(function ($) {

  /* Helper: build an export button config with a Material Symbol icon */
  const exportBtn = (icon, extend, title) => ({
    extend,
    text: '<span class="material-symbols-outlined" '
        + 'style="font-size:16px;vertical-align:-4px;margin-right:2px">'
        + icon + '</span>'
        + extend.charAt(0).toUpperCase() + extend.slice(1),
    className: 'dt-button',
    title,
    exportOptions: {
      columns: ':not(:last-child)',  /* skip the Actions column */
    },
  });

  const TITLE = '{$exportTitle}';

  const table = new DataTable('#patientsTable', {

    /* ── Layout: suppress built-in toolbar; we use our own ── */
    layout: {
      topStart:    null,
      topEnd:      null,
      bottomStart: 'info',
      bottomEnd:   'paging',
    },

    /* ── Export buttons ── */
    buttons: [
      exportBtn('content_copy',   'copy',  TITLE),
      exportBtn('download',       'csv',   TITLE),
      exportBtn('table_view',     'excel', TITLE),
      exportBtn('picture_as_pdf', 'pdf',   TITLE),
      exportBtn('print',          'print', TITLE),
    ],

    pageLength:  25,
    lengthMenu:  [10, 25, 50, 100],
    order:       [[0, 'asc']],
    searching:   true,
    responsive:  false,  /* mobile handled by the PHP card view */

    /* ── Column definitions ── */
    columnDefs: [
      /* Actions column: no sort, no search, no export */
      { targets: -1, orderable: false, searchable: false },
      /* Patient ID: numeric sort */
      { targets: 0, type: 'num' },
      /* Age/DOB column: extract the numeric age for sort */
      {
        targets: 3,
        type: 'num',
        render: function (data, type) {
          if (type === 'sort' || type === 'type') {
            const match = data ? data.match(/(\d+)\s*yrs/) : null;
            return match ? parseInt(match[1], 10) : 0;
          }
          return data;
        }
      },
    ],

    /* ── Post-init: wire up custom toolbar slot + style bottom bar ── */
    initComplete: function () {
      /* Move Buttons widget into the branded toolbar slot */
      this.api().buttons().container().appendTo('#dt-export-slot');

      /* Apply layout classes to the DataTables bottom bar */
      const bottomRow = document.querySelector('div.dt-layout-row:last-child');
      if (bottomRow) {
        bottomRow.classList.add(
          'bg-surface-container',
          'px-4', 'md:px-6', 'py-4',
          'flex', 'flex-col', 'sm:flex-row',
          'items-center', 'justify-between', 'gap-4',
          'border-t', 'border-surface-variant/20'
        );
      }
    },
  });

  /* ── Wire the custom search input to DataTables API ── */
  document.getElementById('dt-custom-search').addEventListener('input', function () {
    table.search(this.value).draw();
  });

})(jQuery);
JS;

$this->registerJs($js, \yii\web\View::POS_END);


$cleanup = <<<CSS
/* ── Additional cleanup ── */
table.dataTable {
    width: 100% !important;
    margin: 0 !important;
    clear: none !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
}

table.dataTable thead th,
table.dataTable thead td {
    padding: 1rem 1.5rem !important;
    background: #f8fafc !important;
    color: #64748b !important;
    font-size: 0.625rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.1em !important;
}

table.dataTable tbody td {
    padding: 1.25rem 1.5rem !important;
}

table.dataTable tbody tr {
    border-bottom: 1px solid #f1f5f9 !important;
}

/* ── Remove DataTables default sorting icons ── */
table.dataTable thead .dt-orderable-asc,
table.dataTable thead .dt-orderable-desc {
    background-image: none !important;
}

/* ── Custom sorting icons using CSS ── */
table.dataTable thead .dt-orderable-asc::after {
    content: '▲' !important;
    font-size: 0.5rem !important;
    margin-left: 0.25rem !important;
    opacity: 0.4 !important;
}

table.dataTable thead .dt-orderable-desc::after {
    content: '▼' !important;
    font-size: 0.5rem !important;
    margin-left: 0.25rem !important;
    opacity: 0.4 !important;
}

table.dataTable thead .dt-ordering-asc::after {
    content: '▲' !important;
    font-size: 0.5rem !important;
    margin-left: 0.25rem !important;
    opacity: 1 !important;
    color: #2563eb !important;
}

table.dataTable thead .dt-ordering-desc::after {
    content: '▼' !important;
    font-size: 0.5rem !important;
    margin-left: 0.25rem !important;
    opacity: 1 !important;
    color: #2563eb !important;
}
CSS;
$this->registerCss($cleanup);

?>