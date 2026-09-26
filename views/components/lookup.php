<?php
$lookupEntity = $lookupEntity ?? '';
$lookupName = $lookupName ?? '';
$lookupLabel = $lookupLabel ?? '';
$lookupDependsOn = $lookupDependsOn ?? '';
$lookupField = $lookupField ?? $lookupName;
?>
<div
    class="lookup"
    data-entity="<?= e($lookupEntity) ?>"
    data-field="<?= e($lookupField) ?>"
    data-depends-on="<?= e($lookupDependsOn) ?>"
>
    <label>
<?= e($lookupLabel) ?>
</label>
    <div class="lookup-box">
        <input
            class="lookup-input"
            type="text"
            autocomplete="off"
            placeholder="اكتب الاسم أو الرقم للبحث..."
        >
        <input
            class="lookup-value"
            type="hidden"
            name="<?= e($lookupName) ?>"
            data-lookup-field="<?= e($lookupField) ?>"
        >
    </div>
    <div class="lookup-results">
</div>
</div>
