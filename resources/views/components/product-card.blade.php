@props(['product'])

<a
    href="{{ auth()->user()->can('view', $product) ? route('filament.member.pages.produto.{product}', $product->id) : $product->redirect_url }}"
    {{ auth()->user()->can('view', $product) ? 'wire:navigate.hover': '' }}
    {{ auth()->user()->cant('view', $product) ? 'target="blank"': '' }}
    class="w-full flex flex-col rounded-xl shadow-lg border border-solid border-primary cursor-pointer hover:-translate-y-2 transition-all overflow-hidden"
>
    <div class="relative">
        <img src="{{ \Illuminate\Support\Facades\Storage::temporaryUrl($product->cover, now()->addMinute()) }}" class="h-[300px] w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-black/0 to-black/40"></div>
    </div>
    <div class="p-5">
        <h3 class="text-xl font-semibold text-white-900">{{ $product->name }}</h3>
        <p class="mt-2 text-white-600">{!! $product->description !!}</p>

        <!-- Métricas -->
        <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-slate-600">
        <span class="inline-flex items-center gap-1">
          <!-- clock -->
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
            <path fill-rule="evenodd" d="M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5zM11.25 6a.75.75 0 011.5 0v5.19l3.28 3.28a.75.75 0 11-1.06 1.06l-3.47-3.47A.75.75 0 0111.25 12V6z" clip-rule="evenodd"/>
          </svg>
          {{ $product->total_duration }}
        </span>

        </div>
        <div class="mt-5">
            <button type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-white shadow hover:bg-primary-darker focus:outline-none">
                <!-- ícone play -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                    <path d="M8.25 5.75a.75.75 0 011.125-.65l9 5.25a.75.75 0 010 1.3l-9 5.25a.75.75 0 01-1.125-.65V5.75z"/>
                </svg>
                @can('view', $product)
                    Acessar conteudo
                @else
                    Conhecer sobre
                @endcan
            </button>
        </div>
    </div>
</a>
