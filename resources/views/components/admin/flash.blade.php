@if (session('success'))
    <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200/80 p-4 text-sm text-emerald-800 flex items-start gap-3 shadow-2xs">
        <x-icon name="check-circle" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" strokeWidth="2" />
        <div class="flex-1 font-medium">{{ session('success') }}</div>
    </div>
@endif

@if (session('error'))
    <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-200/80 p-4 text-sm text-rose-800 flex items-start gap-3 shadow-2xs">
        <x-icon name="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" strokeWidth="2" />
        <div class="flex-1 font-medium">{{ session('error') }}</div>
    </div>
@endif

@if (session('status'))
    <div class="mb-6 rounded-2xl bg-[#EDE7DF] border border-[#D0C5B8] p-4 text-sm text-[#1C1917] flex items-start gap-3 shadow-2xs">
        <x-icon name="check" class="w-5 h-5 text-[#78716C] shrink-0 mt-0.5" strokeWidth="2" />
        <div class="flex-1 font-medium">{{ session('status') }}</div>
    </div>
@endif
