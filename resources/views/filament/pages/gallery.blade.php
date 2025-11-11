<x-filament-panels::page>

  <section>
    <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px;">

      @forelse($this->getImages() as $image)
        <a href="{{ $image['path'] }}" class="glightbox" data-gallery="gallery-{{ $image['owner_name'] }}"
          style="display: block; position: relative; width: 100%; padding-top: 75%; overflow: hidden;">

          <img src="{{ $image['path'] }}" alt="{{ $image['owner_name'] }}" loading="lazy"
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
        </a>
      @empty
        {{-- bloque empty --}}
      @endforelse
    </div>

  @empty($this->getImages()->count())
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
      <x-heroicon-s-x-circle style="width: 64px; height: 64px; color: #a9aeb6; margin-bottom: 1rem;" />
      <h3 style="font-size: 1.25rem; font-weight: 600; color: #404958; margin-bottom: 0.5rem;">
        No hay imágenes
      </h3>
      <p style="color: #a9aeb6; max-width: 28rem;">
        No se han encontrado imágenes para mostrar en la galería.
      </p>
    </div>
  @endempty
</section>

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const lightbox = GLightbox({
        selector: '.glightbox',
        touchNavigation: true,
        loop: true,
        autoplayVideos: true,
        moreText: 'Ver más',
        moreLength: 60,
        closeOnOutsideClick: true,
        zoomable: true,
        draggable: true
      });
    });
  </script>
@endpush

</x-filament-panels::page>
