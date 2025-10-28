@props(['result' => []])

@php
    $copywriting = data_get($result, 'marketing_copywriting', []);
    $headline = data_get($copywriting, 'headline', '');
    $subHeadline = data_get($copywriting, 'sub_headline', '');
    $bodyCopy = data_get($copywriting, 'body_copy', '');
    $socialCaptions = data_get($copywriting, 'social_media_captions', []);
    $emailSubjects = data_get($copywriting, 'email_subject_lines', []);
@endphp

<section class="card space-y-6" x-data="copywritingData()">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="section-title">Marketing Copywriting</h3>
            <p class="text-sm text-slate-500">Konten marketing siap pakai untuk berbagai platform dan kampanye.</p>
        </div>
        <span class="chip">Ready to Use</span>
    </div>

    {{-- Main Copywriting --}}
    @if($headline || $bodyCopy)
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="text-lg font-semibold text-slate-900">Konten Utama</h4>
                <button 
                    @click="copyToClipboard('{{ addslashes($bodyCopy) }}')"
                    class="btn-secondary text-xs"
                    :class="{ 'bg-emerald-100 text-emerald-700': copied.main }"
                >
                    <span x-show="!copied.main">Copy</span>
                    <span x-show="copied.main">Copied!</span>
                </button>
            </div>
            
            @if($headline)
                <div class="rounded-xl border border-slate-100 bg-emerald-50/30 p-4">
                    <p class="text-sm font-medium text-slate-600 mb-1">Headline</p>
                    <p class="text-lg font-semibold text-slate-900">{{ $headline }}</p>
                </div>
            @endif

            @if($subHeadline)
                <div class="rounded-xl border border-slate-100 bg-blue-50/30 p-4">
                    <p class="text-sm font-medium text-slate-600 mb-1">Sub Headline</p>
                    <p class="text-base text-slate-900">{{ $subHeadline }}</p>
                </div>
            @endif

            @if($bodyCopy)
                <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4">
                    <p class="text-sm font-medium text-slate-600 mb-2">Body Copy</p>
                    <p class="text-sm text-slate-700 leading-relaxed">{{ $bodyCopy }}</p>
                </div>
            @endif
        </div>
    @endif

    {{-- Social Media Captions --}}
    @if($socialCaptions && count($socialCaptions) > 0)
        <div class="space-y-4">
            <h4 class="text-lg font-semibold text-slate-900">Social Media Captions</h4>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($socialCaptions as $index => $caption)
                    @php
                        $platform = data_get($caption, 'platform', '');
                        $platformColor = match($platform) {
                            'instagram' => 'bg-pink-100 text-pink-800 border-pink-200',
                            'tiktok' => 'bg-black text-white border-black',
                            'facebook' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'twitter' => 'bg-sky-100 text-sky-800 border-sky-200',
                            default => 'bg-slate-100 text-slate-800 border-slate-200'
                        };
                    @endphp
                    <div class="rounded-xl border border-slate-100 bg-white p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-1 text-xs font-medium rounded-full border {{ $platformColor }}">
                                {{ ucfirst($platform) }}
                            </span>
                            <button 
                                @click="copyToClipboard('{{ addslashes(data_get($caption, 'caption', '')) }}')"
                                class="btn-secondary text-xs"
                                :class="{ 'bg-emerald-100 text-emerald-700': copied.social[{{ $index }}] }"
                            >
                                <span x-show="!copied.social[{{ $index }}]">Copy</span>
                                <span x-show="copied.social[{{ $index }}]">Copied!</span>
                            </button>
                        </div>
                        <p class="text-sm text-slate-700 leading-relaxed">{{ data_get($caption, 'caption', '') }}</p>
                        @if(data_get($caption, 'cta'))
                            <p class="text-xs font-medium text-emerald-600">{{ data_get($caption, 'cta') }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Email Subject Lines --}}
    @if($emailSubjects && count($emailSubjects) > 0)
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="text-lg font-semibold text-slate-900">Email Subject Lines</h4>
                <button 
                    @click="copyToClipboard('{{ addslashes(implode('\n', $emailSubjects)) }}')"
                    class="btn-secondary text-xs"
                    :class="{ 'bg-emerald-100 text-emerald-700': copied.email }"
                >
                    <span x-show="!copied.email">Copy All</span>
                    <span x-show="copied.email">Copied!</span>
                </button>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach($emailSubjects as $index => $subject)
                    <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 bg-slate-50/60">
                        <button 
                            @click="copyToClipboard('{{ addslashes($subject) }}')"
                            class="btn-secondary text-xs shrink-0"
                            :class="{ 'bg-emerald-100 text-emerald-700': copied.emailSubjects[{{ $index }}] }"
                        >
                            <span x-show="!copied.emailSubjects[{{ $index }}]">Copy</span>
                            <span x-show="copied.emailSubjects[{{ $index }}]">✓</span>
                        </button>
                        <p class="text-sm text-slate-700">{{ $subject }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>

<script>
function copywritingData() {
    return {
        copied: {
            main: false,
            social: {},
            email: false,
            emailSubjects: {}
        },
        
        async copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                this.showCopiedFeedback();
            } catch (err) {
                console.error('Failed to copy: ', err);
                // Fallback for older browsers
                this.fallbackCopyTextToClipboard(text);
            }
        },
        
        fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                this.showCopiedFeedback();
            } catch (err) {
                console.error('Fallback: Oops, unable to copy', err);
            }
            
            document.body.removeChild(textArea);
        },
        
        showCopiedFeedback() {
            // This will be handled by individual button states
        }
    }
}
</script>
