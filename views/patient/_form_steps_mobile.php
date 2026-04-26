<!-- MOBILE STEPPER: outside flex-row, sticky works against full page height -->
<div id="form-stepper-mobile"
     class="block lg:hidden sticky top-16 z-30
            bg-background -mx-4 px-4 md:-mx-8 md:px-8
            border-b border-surface-container-high
            shadow-[0_4px_12px_rgba(0,26,72,0.06)]
            pb-3 mb-4">
    <div class="flex flex-row gap-3 overflow-x-auto no-scrollbar">
        <!-- step buttons here -->
        <?= $this->render('_steps') ?>
    </div>
</div>