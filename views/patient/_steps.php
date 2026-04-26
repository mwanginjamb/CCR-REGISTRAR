  <!-- STEP 1 -->
        <button
            type="button"
            data-target="patient-information"
            class="form-step active-step flex items-start gap-3 md:gap-4 p-3 md:p-4 rounded-xl bg-surface-container-lowest shadow-[0_12px_32px_rgba(0,26,72,0.06)] border-l-4 border-primary min-w-[160px] lg:min-w-0 flex-1 lg:flex-none text-left transition-all"
        >
            <div class="step-number w-7 h-7 md:w-8 md:h-8 rounded-lg bg-primary flex items-center justify-center text-white text-sm font-bold shrink-0">
                1
            </div>

            <div>
                <h4 class="step-title text-xs md:text-sm font-bold text-primary whitespace-nowrap">
                    Patient Info
                </h4>

                <p class="text-[9px] md:text-[10px] text-on-surface-variant uppercase tracking-wider font-semibold mt-0.5">
                    Step 1 of 3
                </p>
            </div>
        </button>

        <!-- STEP 2 -->
        <button
            type="button"
            data-target="tumour-details"
            class="form-step inactive-step flex items-start gap-3 md:gap-4 p-3 md:p-4 rounded-xl bg-surface-container min-w-[160px] lg:min-w-0 flex-1 lg:flex-none text-left transition-all"
        >
            <div class="step-number w-7 h-7 md:w-8 md:h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-outline text-sm font-bold shrink-0">
                2
            </div>

            <div>
                <h4 class="step-title text-xs md:text-sm font-medium text-on-surface-variant whitespace-nowrap">
                    Tumour Details
                </h4>

                <p class="text-[9px] md:text-[10px] text-outline uppercase tracking-wider font-semibold mt-0.5">
                    Step 2 of 3
                </p>
            </div>
        </button>

        <!-- STEP 3 -->
        <button
            type="button"
            data-target="treatment-followup"
            class="form-step inactive-step flex items-start gap-3 md:gap-4 p-3 md:p-4 rounded-xl bg-surface-container min-w-[160px] lg:min-w-0 flex-1 lg:flex-none text-left transition-all"
        >
            <div class="step-number w-7 h-7 md:w-8 md:h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-outline text-sm font-bold shrink-0">
                3
            </div>

            <div>
                <h4 class="step-title text-xs md:text-sm font-medium text-on-surface-variant whitespace-nowrap">
                    Treatment
                </h4>

                <p class="text-[9px] md:text-[10px] text-outline uppercase tracking-wider font-semibold mt-0.5">
                    Step 3 of 3
                </p>
            </div>
        </button>
