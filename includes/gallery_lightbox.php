<!-- Gallery photo lightbox — shared by any page with a data-gallery-lightbox
     trigger on it (Facilities, Blog, Gallery). Wired up by assets/js/main.js
     "GALLERY LIGHTBOX", which is element-existence-guarded and needs no
     per-page changes. -->
<div id="gallery-lightbox" class="lightbox" role="dialog" aria-modal="true" aria-label="Photo preview">
    <button id="gallery-lightbox-close"
            class="absolute top-5 right-5 grid h-10 w-10 place-items-center rounded-xl text-white/60 hover:text-white transition z-10"
            style="background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20);"
            aria-label="Close">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
    <button id="gallery-lightbox-prev"
            class="absolute left-4 top-1/2 -translate-y-1/2 grid h-10 w-10 place-items-center rounded-xl text-white/60 hover:text-white transition z-10"
            style="background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20);"
            aria-label="Previous image">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
    <button id="gallery-lightbox-next"
            class="absolute right-4 top-1/2 -translate-y-1/2 grid h-10 w-10 place-items-center rounded-xl text-white/60 hover:text-white transition z-10"
            style="background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20);"
            aria-label="Next image">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </button>
    <img id="gallery-lightbox-img" src="" alt="" class="lightbox-img">
    <p id="gallery-lightbox-caption" class="lightbox-caption"></p>
</div>
