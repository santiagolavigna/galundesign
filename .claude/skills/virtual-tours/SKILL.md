---
name: virtual-tours
description: Marzipano 360 virtual tours - scene/hotspot structure, tile generation, custom extensions, product catalogs inside tours, performance. Use when building or modifying virtual tours (Marzipano data.js, tiles, panoramas).
---

# Virtual Tours — Marzipano

Reference implementations: mars1pano2 (tour builder + product hotspots), OLIVO/vr (exported
tour embedded in a site), VR-LASERPRO.

## Anatomy of an exported Marzipano tour

```
tour/
├── index.html          <- viewer shell
├── index.js            <- viewer logic (scene switching, hotspot rendering, autorotate)
├── data.js             <- THE tour definition: scenes, levels, initial view, hotspots
├── tiles/<scene-id>/   <- multi-resolution cube tiles ({z}/{f}/{y}/{x}.jpg) + preview.jpg
└── vendor/             <- marzipano.js, screenfull, bowser (DO NOT EDIT)
```

`data.js` structure per scene: `id`, `name`, `levels` (tile resolutions), `faceSize`,
`initialViewParameters` (yaw/pitch/fov), `linkHotspots` (navigation, `target` = scene id),
`infoHotspots` (title + text).

## Golden rules

1. **Never edit viewer bundles or vendor files.** Extend via separate patch JS files loaded
   after the main script (the `mars2-*.js` pattern in mars1pano2). Keeps upgrades possible.
2. `data.js` is data — edit it (or generate it) rather than hardcoding scenes in JS.
3. Scene ids: stable slugs (`0-recepcion`, `1-salon-principal`); tiles folder must match.
4. Every scene needs a `preview.jpg` — it's what the user sees while tiles load.

## Custom hotspot types (beyond link/info)

Add a custom array to the scene in `data.js` (e.g. `productHotspots`) plus a patch file that
reads it and creates DOM hotspots:

```js
scene.hotspotContainer().createHotspot(el, { yaw: h.yaw, pitch: h.pitch });
```

Keep hotspot content data-driven (e.g. `products-catalog.json` with id, name, price, image,
WhatsApp link) so the client can update products without touching tour code. Product/info
modals: build DOM with `textContent` (never innerHTML with catalog strings), Spanish UI,
mobile-friendly (bottom sheet on small screens).

## Getting yaw/pitch for a new hotspot

Temporarily log coordinates on click, then copy the values into `data.js`:

```js
viewer.domElement().addEventListener('click', (e) => {
    console.log(view.screenToCoordinates({ x: e.clientX, y: e.clientY }));
});
```

## Tiles & performance

- Source: equirectangular pano (Insta360 X4 export, ideally 8K). Generate cube tiles with the
  Marzipano tool workflow (mars1pano2 includes `convert_tiles.py` for multi-res conversion).
- Tile size 512, JPEG quality ~80; total tour weight matters on mobile data — compress
  aggressively, previews <= 60KB.
- Many scenes (>15): lazy-define scenes and prefetch only linked neighbors.
- Test on a real phone: gyroscope control, fullscreen button, hotspot tap targets >= 44px.

## Embedding a tour in a client site (OLIVO pattern)

- Tour lives self-contained in `/vr/` (or `/tour/`); link or iframe from the main site.
- Give it a branded loading screen and a visible "Volver al sitio" exit.
- If the site is PHP, the tour stays static — don't couple it to the backend; feed dynamic
  data via a JSON endpoint if needed.
