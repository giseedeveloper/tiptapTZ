<x-guest-layout title="TIPTAP | Waiter Registration" :wide="true">
    @php
        $initialStep = 1;
        if ($errors->has('first_name') || $errors->has('last_name')) {
            $initialStep = 1;
        } elseif ($errors->has('email') || $errors->has('phone')) {
            $initialStep = 2;
        } elseif ($errors->has('location')) {
            $initialStep = 3;
        } elseif ($errors->has('password')) {
            $initialStep = 4;
        }

        $inputClass = 'block w-full px-4 py-3.5 bg-[#F5F3FF] border border-[#DDD7FE] rounded-xl font-medium text-[#12141C] placeholder-[#64708B]/50 focus:ring-2 focus:ring-[#8C71F6] focus:border-transparent transition-all text-sm sm:text-base';
        $bubbleClass = 'bg-[#F5F3FF] border border-[#DDD7FE] rounded-2xl rounded-tl-none p-4 text-[#12141C] font-medium mb-4';
    @endphp

    <div class="max-w-xl mx-auto">
        <div class="text-center mb-6">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#F5F3FF] text-[#6D52E8] text-[10px] font-bold uppercase tracking-wider border border-[#DDD7FE] mb-4">
                <span class="w-1.5 h-1.5 bg-[#8C71F6] rounded-full animate-pulse"></span>
                Kwa Wahudumu
            </span>
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[#F5F3FF] flex items-center justify-center border border-[#DDD7FE] shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-[#8C71F6]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#12141C] tracking-tight">Sajili kama Waiter</h1>
            <p class="text-[#64708B] text-sm mt-2 max-w-sm mx-auto leading-relaxed">
                Hatua 4 tu — pata nambari yako ya pekee. Manager atakuunga na restaurant.
            </p>
        </div>

        <div class="mb-6 bg-[#F5F3FF] h-2 rounded-full overflow-hidden border border-[#DDD7FE]">
            <div id="progress-bar" class="bg-gradient-to-r from-[#8C71F6] to-[#6D52E8] h-full transition-all duration-500" style="width: 25%"></div>
        </div>
        <p id="step-label" class="text-center text-xs font-bold uppercase tracking-wider text-[#64708B] mb-6">Hatua 1 ya 4</p>

        @if (session('status'))
            <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m22 4-2.09 2.09"/></svg>
                {{ session('status') }}
            </div>
        @endif

        <form id="waiter-registration-form" method="POST" action="{{ route('waiter.register.store') }}">
            @csrf

            <div class="step step-active" data-step="1">
                <div class="{{ $bubbleClass }}">Tuanze na jina lako kamili.</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="text-[10px] font-bold uppercase tracking-wider text-[#64708B] mb-1.5 block">Jina la kwanza</label>
                        <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required autofocus class="{{ $inputClass }}" placeholder="Jina la kwanza">
                        <x-input-error :messages="$errors->get('first_name')" class="mt-1.5 text-xs" />
                    </div>
                    <div>
                        <label for="last_name" class="text-[10px] font-bold uppercase tracking-wider text-[#64708B] mb-1.5 block">Jina la mwisho</label>
                        <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required class="{{ $inputClass }}" placeholder="Jina la mwisho">
                        <x-input-error :messages="$errors->get('last_name')" class="mt-1.5 text-xs" />
                    </div>
                </div>
            </div>

            <div class="step step-hidden" data-step="2">
                <div class="{{ $bubbleClass }}">Tutahitaji barua pepe na nambari ya simu yako.</div>
                <div class="space-y-4">
                    <div>
                        <label for="email" class="text-[10px] font-bold uppercase tracking-wider text-[#64708B] mb-1.5 block">Barua pepe</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required class="{{ $inputClass }}" placeholder="mfano@barua.com">
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs" />
                    </div>
                    <div>
                        <label for="phone" class="text-[10px] font-bold uppercase tracking-wider text-[#64708B] mb-1.5 block">Nambari ya simu</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required class="{{ $inputClass }}" placeholder="0712 345 678">
                        <x-input-error :messages="$errors->get('phone')" class="mt-1.5 text-xs" />
                    </div>
                </div>
            </div>

            <div class="step step-hidden" data-step="3">
                <div class="{{ $bubbleClass }}">Uko wapi? (Si lazima — unaweza kuruka hatua hii.)</div>
                <div>
                    <label for="location" class="text-[10px] font-bold uppercase tracking-wider text-[#64708B] mb-1.5 block">Mahali</label>
                    <input id="location" type="text" name="location" value="{{ old('location') }}" class="{{ $inputClass }}" placeholder="Mfano: Dar es Salaam">
                    <x-input-error :messages="$errors->get('location')" class="mt-1.5 text-xs" />
                </div>
            </div>

            <div class="step step-hidden" data-step="4">
                <div class="{{ $bubbleClass }}">Mwisho — weka neno la siri imara (angalau herufi 8).</div>
                <div class="space-y-4">
                    <div>
                        <label for="password" class="text-[10px] font-bold uppercase tracking-wider text-[#64708B] mb-1.5 block">Neno la siri</label>
                        <input id="password" type="password" name="password" required class="{{ $inputClass }}" placeholder="Neno la siri">
                        <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs" />
                    </div>
                    <div>
                        <label for="password_confirmation" class="text-[10px] font-bold uppercase tracking-wider text-[#64708B] mb-1.5 block">Thibitisha neno la siri</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required class="{{ $inputClass }}" placeholder="Thibitisha">
                    </div>
                </div>
            </div>

            <div class="mt-10 flex items-center justify-between gap-3">
                <button type="button" id="prev-btn" class="hidden text-[#64708B] font-bold hover:text-[#12141C] transition-colors flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    Rudi
                </button>
                <button type="button" id="next-btn" class="btn-fin text-white px-8 py-3.5 rounded-xl font-bold text-sm sm:text-base flex items-center gap-2 ml-auto">
                    Endelea
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                </button>
                <button type="submit" id="submit-btn" class="hidden bg-gradient-to-r from-emerald-500 to-[#6D52E8] text-white px-8 py-3.5 rounded-xl font-bold text-sm sm:text-base shadow-lg shadow-emerald-500/20 hover:scale-[1.01] active:scale-[0.99] transition-all flex items-center gap-2 ml-auto">
                    Fungua Akaunti
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
            </div>
            <p class="text-center text-[#64708B] text-xs mt-3">Bila malipo · Utapata code yako ya pekee mara moja</p>
        </form>

        <div class="border-t border-[#DDD7FE] pt-6 mt-8 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-6">
            <a href="{{ route('login') }}" class="text-[#8C71F6] font-semibold text-sm hover:text-[#6D52E8] transition-colors">
                Tayari una akaunti? Ingia
            </a>
            <span class="hidden sm:inline w-px h-4 bg-[#DDD7FE]"></span>
            <a href="{{ route('restaurant.register') }}" class="text-[#64708B] text-sm hover:text-[#12141C] transition-colors">
                Sajili Restaurant (Manager)
            </a>
        </div>
    </div>

    <style>
        .step-hidden { display: none; }
        .step-active { display: block; animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const totalSteps = 4;
            const steps = document.querySelectorAll('.step');
            const nextBtn = document.getElementById('next-btn');
            const prevBtn = document.getElementById('prev-btn');
            const submitBtn = document.getElementById('submit-btn');
            const progressBar = document.getElementById('progress-bar');
            const stepLabel = document.getElementById('step-label');
            let currentStep = {{ $initialStep }};

            const optionalSteps = [3];

            function getStepInputs(stepEl) {
                return Array.from(stepEl.querySelectorAll('input')).filter((input) => input.type !== 'hidden');
            }

            function validateStep(stepNumber) {
                const stepEl = steps[stepNumber - 1];
                const inputs = getStepInputs(stepEl);
                let valid = true;

                inputs.forEach((input) => {
                    input.classList.remove('border-rose-500', 'ring-2', 'ring-rose-500/50');
                    if (optionalSteps.includes(stepNumber)) {
                        return;
                    }
                    if (input.hasAttribute('required') && input.value.trim() === '') {
                        input.classList.add('border-rose-500', 'ring-2', 'ring-rose-500/50');
                        valid = false;
                    }
                });

                if (!valid) {
                    const firstInvalid = inputs.find((input) => input.classList.contains('border-rose-500'));
                    firstInvalid?.focus();
                }

                return valid;
            }

            function updateUI() {
                steps.forEach((step) => {
                    const n = parseInt(step.dataset.step, 10);
                    if (n === currentStep) {
                        step.classList.remove('step-hidden');
                        step.classList.add('step-active');
                    } else {
                        step.classList.add('step-hidden');
                        step.classList.remove('step-active');
                    }
                });

                progressBar.style.width = `${(currentStep / totalSteps) * 100}%`;
                stepLabel.textContent = `Hatua ${currentStep} ya ${totalSteps}`;

                prevBtn.classList.toggle('hidden', currentStep === 1);
                nextBtn.classList.toggle('hidden', currentStep === totalSteps);
                submitBtn.classList.toggle('hidden', currentStep !== totalSteps);
            }

            nextBtn.addEventListener('click', () => {
                if (!validateStep(currentStep)) {
                    return;
                }
                if (currentStep < totalSteps) {
                    currentStep++;
                    updateUI();
                    const nextInput = getStepInputs(steps[currentStep - 1])[0];
                    if (nextInput) {
                        setTimeout(() => nextInput.focus(), 250);
                    }
                }
            });

            prevBtn.addEventListener('click', () => {
                if (currentStep > 1) {
                    currentStep--;
                    updateUI();
                }
            });

            document.addEventListener('keypress', function(e) {
                if (e.key !== 'Enter') {
                    return;
                }
                if (currentStep < totalSteps) {
                    e.preventDefault();
                    nextBtn.click();
                }
            });

            updateUI();
            const activeInput = getStepInputs(steps[currentStep - 1])[0];
            if (activeInput) {
                setTimeout(() => activeInput.focus(), 100);
            }
        });
    </script>
</x-guest-layout>
