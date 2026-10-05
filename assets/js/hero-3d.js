import * as THREE from 'three';

const canvas = document.getElementById('hero-scene');
const hero = document.getElementById('intro');
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (!canvas || !hero || reduce) {
    if (canvas) {
        canvas.remove();
    }
} else {
    const mobileQuery = window.matchMedia('(max-width: 799px)');
    const isMobile = () => mobileQuery.matches;

    const renderer = new THREE.WebGLRenderer({
        canvas,
        alpha: true,
        antialias: true,
        powerPreference: 'high-performance',
    });
    renderer.setClearColor(0x000000, 0);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isMobile() ? 1.25 : 1.5));

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(40, 1, 0.1, 100);
    camera.position.z = 9;

    const root = new THREE.Group();
    scene.add(root);

    const lineColor = new THREE.Color('#b7b2aa');
    const accentColor = new THREE.Color('#d6ff3f');

    const lineMat = (opacity) => new THREE.LineBasicMaterial({
        color: lineColor,
        transparent: true,
        opacity,
        depthWrite: false,
    });

    const lattice = new THREE.Group();
    {
        const pts = [];
        const step = 0.62;
        const span = isMobile() ? 1 : 2;
        for (let x = -span; x <= span; x += 1) {
            for (let y = -span; y <= span; y += 1) {
                for (let z = -span; z <= span; z += 1) {
                    if (Math.abs(x) + Math.abs(y) + Math.abs(z) > span + 1) {
                        continue;
                    }
                    pts.push(new THREE.Vector3(x * step, y * step, z * step));
                }
            }
        }
        const edges = [];
        for (let i = 0; i < pts.length; i += 1) {
            for (let j = i + 1; j < pts.length; j += 1) {
                if (pts[i].distanceTo(pts[j]) < step * 1.05) {
                    edges.push(pts[i], pts[j]);
                }
            }
        }
        lattice.add(new THREE.LineSegments(
            new THREE.BufferGeometry().setFromPoints(edges),
            lineMat(0.55),
        ));
        lattice.add(new THREE.Points(
            new THREE.BufferGeometry().setFromPoints(pts),
            new THREE.PointsMaterial({
                color: accentColor,
                size: 0.09,
                transparent: true,
                opacity: 0.9,
                depthWrite: false,
                sizeAttenuation: true,
            }),
        ));
    }
    root.add(lattice);

    const loop = new THREE.Group();
    const knotGeo = new THREE.TorusKnotGeometry(0.95, 0.26, isMobile() ? 90 : 160, isMobile() ? 8 : 12, 2, 3);
    loop.add(new THREE.LineSegments(new THREE.WireframeGeometry(knotGeo), lineMat(0.32)));
    const knotCurve = new THREE.TorusKnotGeometry(0.95, 0.001, 240, 3, 2, 3);
    const pulse = new THREE.Mesh(
        new THREE.SphereGeometry(0.085, 16, 16),
        new THREE.MeshBasicMaterial({ color: accentColor, transparent: true, opacity: 1 }),
    );
    loop.add(pulse);
    root.add(loop);

    const globe = new THREE.Group();
    const sphere = new THREE.SphereGeometry(1.15, isMobile() ? 16 : 24, isMobile() ? 12 : 18);
    globe.add(new THREE.LineSegments(new THREE.WireframeGeometry(sphere), lineMat(0.34)));
    const ring = new THREE.Mesh(
        new THREE.TorusGeometry(1.62, 0.012, 8, 96),
        new THREE.MeshBasicMaterial({ color: accentColor, transparent: true, opacity: 0.75 }),
    );
    ring.rotation.x = Math.PI / 2.2;
    globe.add(ring);
    root.add(globe);

    const pieces = [lattice, loop, globe];
    pieces.forEach((piece) => {
        piece.scale.setScalar(0.001);
    });

    const pointer = { x: 0, y: 0, tx: 0, ty: 0 };
    let inView = true;
    let frame = 0;
    const knotPos = knotCurve.attributes.position;
    const sample = new THREE.Vector3();

    const resize = () => {
        const width = canvas.clientWidth;
        const height = canvas.clientHeight;
        if (width === 0 || height === 0) {
            return;
        }
        camera.aspect = width / height;
        camera.position.z = isMobile() ? 12 : 9;
        camera.updateProjectionMatrix();
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isMobile() ? 1.25 : 1.5));
        renderer.setSize(width, height, false);
    };

    window.addEventListener('pointermove', (event) => {
        pointer.tx = (event.clientX / window.innerWidth - 0.5) * 2;
        pointer.ty = (event.clientY / window.innerHeight - 0.5) * 2;
    }, { passive: true });
    window.addEventListener('resize', resize);
    mobileQuery.addEventListener('change', resize);
    const canvasSize = new ResizeObserver(resize);
    canvasSize.observe(canvas);
    resize();

    const heroView = new IntersectionObserver((entries) => {
        inView = entries.some((entry) => entry.isIntersecting);
    });
    heroView.observe(hero);

    const targetFor = (index, time) => {
        const mobile = isMobile();
        const halfH = Math.tan(THREE.MathUtils.degToRad(camera.fov / 2)) * camera.position.z;
        const halfW = halfH * camera.aspect;
        const angle = (index * Math.PI * 2) / 3 + time * 0.12;
        const reachX = halfW * (mobile ? 0.48 : 0.72);
        const reachY = Math.min(halfH * (mobile ? 0.28 : 0.42), reachX * (mobile ? 0.72 : 0.62));
        return {
            x: Math.sin(angle) * reachX,
            y: Math.cos(angle) * reachY,
            z: -1.2 + Math.cos(angle) * 0.7,
            s: mobile ? 0.42 : 0.78,
        };
    };

    const tick = (time) => {
        frame = requestAnimationFrame(tick);
        if (!inView || document.visibilityState === 'hidden') {
            return;
        }
        const t = time * 0.001;
        pointer.x += (pointer.tx - pointer.x) * 0.05;
        pointer.y += (pointer.ty - pointer.y) * 0.05;

        pieces.forEach((piece, index) => {
            const goal = targetFor(index, t);
            piece.position.x += (goal.x - piece.position.x) * 0.045;
            piece.position.y += (goal.y - piece.position.y) * 0.045;
            piece.position.z += (goal.z - piece.position.z) * 0.045;
            const next = piece.scale.x + (goal.s - piece.scale.x) * 0.045;
            piece.scale.setScalar(Math.max(next, 0.001));
        });

        lattice.rotation.x = t * 0.18;
        lattice.rotation.y = t * 0.24;
        loop.rotation.y = t * 0.2;
        loop.rotation.z = t * 0.1;
        globe.rotation.y = t * 0.3;
        ring.rotation.z = t * 0.4;

        const idx = Math.floor((t * 40) % knotPos.count);
        sample.fromBufferAttribute(knotPos, idx);
        pulse.position.copy(sample);

        root.rotation.y = pointer.x * 0.22;
        root.rotation.x = pointer.y * 0.12;

        renderer.render(scene, camera);
    };

    frame = requestAnimationFrame(tick);

    window.addEventListener('pagehide', () => {
        cancelAnimationFrame(frame);
        heroView.disconnect();
        canvasSize.disconnect();
        renderer.dispose();
    });
}
