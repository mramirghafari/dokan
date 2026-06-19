<style>
/* Dokan panel onboarding — inline so it always loads */
.ob-shell { width: 100%; margin-bottom: 1.5rem; }

.ob-hero {
    position: relative;
    overflow: hidden;
    border-radius: 1.25rem;
    background:
        radial-gradient(ellipse 42% 58% at 88% 16%, rgba(249,186,22,.48) 0%, rgba(249,186,22,.08) 58%, rgba(249,186,22,0) 82%),
        radial-gradient(ellipse 68% 26% at 86% 8%, rgba(255,225,138,.34) 0%, rgba(255,225,138,0) 74%),
        linear-gradient(110deg, rgba(249,186,22,.28) 8%, rgba(249,186,22,0) 36%),
        radial-gradient(ellipse 56% 75% at 10% 100%, rgba(255,255,255,.12) 0%, rgba(255,255,255,0) 78%),
        linear-gradient(130deg, #3f2e72 0%, #543C92 56%, #6a54aa 100%);
    color: #fff;
    box-shadow: 0 20px 44px rgba(59, 47, 110, 0.23);
    margin-bottom: 1.25rem;
}
.ob-hero__inner { position: relative; z-index: 1; padding: 1.6rem 1.65rem 1.35rem; }
.ob-hero__badge {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .4rem .9rem; border-radius: 999px;
    background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.22);
    font-size: .82rem; font-weight: 600; margin-bottom: 1rem;
}
.ob-hero__title { margin: 0 0 .65rem; font-size: 1.75rem; font-weight: 800; color: #fff !important; line-height: 1.5; }
.ob-hero__text { margin: 0 0 1.15rem; max-width: 680px; color: rgba(255,255,255,.9) !important; line-height: 1.9; font-size: .95rem; }
.ob-hero__summary {
    margin-top: .1rem;
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(300px, .92fr);
    gap: 1.15rem;
    align-items: start;
}
.ob-hero__col { min-width: 0; }
.ob-hero__col--right { display: grid; align-content: start; gap: .55rem; }
.ob-hero__col--left { display: grid; align-content: start; gap: .5rem; margin-top: -.05rem; }
.ob-hero__progress-wrap {
    margin-top: 0;
}
.ob-hero__graphic {
    width: 100%;
    max-width: 320px;
    margin: 0 auto;
    padding: .1rem 0 0;
    opacity: .9;
    display: flex;
    justify-content: center;
}
.ob-hero__graphic svg {
    width: min(100%, 280px);
    height: auto;
    display: block;
    filter: drop-shadow(0 8px 18px rgba(47, 31, 88, .25));
}
.ob-hero__progress-label {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: .45rem; font-size: .9rem; font-weight: 600;
}
.ob-hero__progress-label span:last-child { font-size: .95rem; font-weight: 800; }
.ob-hero__progress {
    height: 22px; min-height: 22px; border-radius: 999px;
    background: rgba(255,255,255,.22) !important;
    box-shadow: inset 0 1px 3px rgba(0,0,0,.12);
}
.ob-hero__progress .progress-bar {
    border-radius: 999px;
    background: linear-gradient(90deg, #fff 0%, #d4f5e4 100%) !important;
    color: #2f2860; font-size: .78rem; font-weight: 800;
    line-height: 22px; min-width: 2.5rem;
    box-shadow: 0 2px 8px rgba(255,255,255,.25);
}
.ob-hero__stats {
    background: linear-gradient(145deg, rgba(255,255,255,.22), rgba(255,255,255,.09));
    border: 1px solid rgba(255,255,255,.28);
    border-radius: 1rem; padding: .72rem .95rem .74rem; height: 100%;
    backdrop-filter: blur(8px);
    box-shadow: 0 14px 28px rgba(27, 19, 58, .18), inset 0 1px 0 rgba(255,255,255,.24);
}
.ob-hero__stats li {
    display: flex; justify-content: space-between; align-items: center;
    gap: .85rem; padding: .42rem 0; border-top: 1px solid rgba(255,255,255,.18);
    font-size: .9rem;
}
.ob-hero__stats li:first-child { border-top: 0; padding-top: 0; }
.ob-hero__stats li:last-child { padding-bottom: 0; }
.ob-hero__stat-label {
    display: inline-flex; align-items: center; gap: .58rem;
    color: rgba(255,255,255,.96); font-weight: 650;
    padding-right: .35rem;
}
.ob-hero__stat-label i {
    width: 1.72rem; height: 1.72rem; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,.22); font-size: 1rem; flex-shrink: 0;
    margin-left: .15rem;
}
.ob-hero__stats strong {
    font-size: 1.2rem; font-weight: 800; color: #fff;
    text-shadow: 0 1px 6px rgba(0,0,0,.2);
    min-width: 1.5rem; text-align: left;
}
.ob-hero__side {
    margin-top: 0;
    display: grid;
    align-content: start;
    gap: .45rem;
}
.ob-hero__actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .4rem;
    margin-top: .1rem;
}
.ob-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: .48rem;
    border-radius: .86rem; padding: .64rem 1.08rem; font-size: .84rem; font-weight: 700;
    line-height: 1.5; text-decoration: none !important; border: 1px solid transparent;
    transition: all .22s ease;
}
.ob-btn i { font-size: 1rem; }
.ob-btn:focus-visible {
    outline: none;
    box-shadow: 0 0 0 .2rem rgba(84,60,146,.22), 0 10px 24px rgba(38, 29, 77, .22);
}
.ob-btn--primary {
    background: linear-gradient(135deg, #6a54aa 0%, #543C92 58%, #45317a 100%);
    border-color: rgba(255,255,255,.22);
    color: #fff !important;
    box-shadow: 0 11px 24px rgba(50, 34, 93, .34), inset 0 1px 0 rgba(255,255,255,.22);
}
.ob-btn--primary:hover {
    background: linear-gradient(135deg, #715db1 0%, #5a4497 58%, #4a3681 100%);
    border-color: rgba(255,255,255,.28);
    color: #fff !important;
    transform: translateY(-2px);
}
.ob-btn--primary:active {
    background: linear-gradient(135deg, #644fa2 0%, #4c397f 100%) !important;
    border-color: rgba(255,255,255,.2) !important;
    transform: translateY(0);
}
.ob-btn--accent {
    background: #F9BA16;
    border-color: #F9BA16;
    color: #3f2e72 !important;
    box-shadow: 0 8px 18px rgba(249,186,22,.34);
}
.ob-btn--accent:hover {
    background: #e5a500;
    border-color: #e5a500;
    color: #352760 !important;
    transform: translateY(-1px);
}
.ob-btn--accent:active {
    background: #d99900 !important;
    border-color: #d99900 !important;
    transform: translateY(0);
}

.ob-grid {
    display: grid; gap: 1rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}
@media (max-width: 991px) { .ob-grid { grid-template-columns: 1fr; } }

.ob-step {
    display: flex; gap: 1rem; align-items: stretch;
    background: #fff; border: 1px solid #e8e6f0; border-radius: 1rem;
    padding: 1.2rem 1.25rem;
    box-shadow: 0 6px 20px rgba(36,32,54,.05);
    transition: transform .2s, box-shadow .2s;
}
.ob-step:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(36,32,54,.09); }
.ob-step--done { border-color: rgba(36,130,48,.3); background: linear-gradient(135deg,#fff,#f3fbf6); }
.ob-step--active { border-color: rgba(82,69,149,.4); box-shadow: 0 12px 32px rgba(82,69,149,.14); }
.ob-step__num {
    flex: 0 0 2.5rem; width: 2.5rem; height: 2.5rem; border-radius: .75rem;
    display: flex; align-items: center; justify-content: center;
    background: rgba(82,69,149,.1); color: #524595; font-weight: 800; font-size: .9rem;
}
.ob-step--done .ob-step__num { background: #248230; color: #fff; }
.ob-step__main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.ob-step__head { display: flex; gap: .85rem; margin-bottom: .85rem; }
.ob-step__icon {
    flex: 0 0 2.6rem; width: 2.6rem; height: 2.6rem; border-radius: .75rem;
    display: flex; align-items: center; justify-content: center;
    background: rgba(82,69,149,.08); color: #524595; font-size: 1.2rem;
}
.ob-step--done .ob-step__icon { background: rgba(36,130,48,.1); color: #248230; }
.ob-step__head h5 { margin: 0 0 .3rem; font-size: 1rem; font-weight: 700; color: #2f2b3d; }
.ob-step__head p { margin: 0; font-size: .86rem; color: #7a7786; line-height: 1.7; }
.ob-step__foot {
    margin-top: auto; padding-top: .85rem; border-top: 1px dashed #ebe9f1;
    display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap;
}
.ob-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .3rem .7rem; border-radius: 999px; font-size: .76rem; font-weight: 700;
}
.ob-badge--done { background: #e8f8ef; color: #1a7a4c; }
.ob-badge--active { background: rgba(82,69,149,.1); color: #524595; }
.ob-badge--wait { background: #fff6e5; color: #b87a00; }
.ob-link {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .5rem 1rem; border-radius: .6rem; font-size: .82rem; font-weight: 700;
    text-decoration: none !important; background: #f4f3f8; color: #6e6b7b !important;
}
.ob-link--primary {
    background: linear-gradient(135deg,#524595,#7b6bb8) !important;
    color: #fff !important; box-shadow: 0 6px 16px rgba(82,69,149,.28);
}
@media (max-width: 991px) {
    .ob-hero__inner { padding: 1.35rem 1.2rem 1.2rem; }
    .ob-hero__summary {
        grid-template-columns: 1fr;
        gap: .75rem;
    }
    .ob-hero__graphic {
        max-width: 240px;
        margin-bottom: 0;
        margin-inline: auto;
        padding-top: .05rem;
    }
    .ob-hero__side {
        margin-top: 0;
    }
    .ob-hero__actions .ob-btn { width: 100%; }
}

/* Welcome modal */
#panelWelcomeModal .modal-content { border: 0 !important; border-radius: 1.25rem !important; overflow: hidden; box-shadow: 0 28px 64px rgba(20,18,32,.28) !important; }
#panelWelcomeModal .modal-dialog { max-width: 440px; }
.ob-modal__head {
    background: linear-gradient(145deg, #322a5c 0%, #524595 50%, #6a5acd 100%);
    color: #fff; padding: 1.5rem 1.5rem 1.25rem; text-align: center; position: relative;
}
.ob-modal__close {
    position: absolute; top: 1rem; left: 1rem;
    width: 2rem; height: 2rem; border: 0; border-radius: 50%;
    background: rgba(255,255,255,.18); color: #fff; font-size: 1.1rem;
    display: flex; align-items: center; justify-content: center; cursor: pointer;
    line-height: 1; padding: 0;
}
.ob-modal__close:hover { background: rgba(255,255,255,.3); }
.ob-modal__eyebrow {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .35rem .75rem; border-radius: 999px;
    background: rgba(255,255,255,.14); font-size: .78rem; font-weight: 600; margin-bottom: 1rem;
}
.ob-modal__icon {
    width: 4.5rem; height: 4.5rem; margin: 0 auto 1rem; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.22);
    font-size: 2.2rem; color: #fff;
}
.ob-modal__title { margin: 0 0 .65rem; font-size: 1.35rem; font-weight: 800; color: #fff !important; line-height: 1.55; }
.ob-modal__body { padding: 1.25rem 1.5rem; background: #fff; }
.ob-modal__text { margin: 0 0 1rem; color: #5e5b6a !important; font-size: .9rem; line-height: 1.9; text-align: right; }
.ob-modal__features { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: .5rem; }
.ob-modal__features li {
    display: flex; align-items: center; gap: .55rem;
    padding: .55rem .85rem; border-radius: .65rem;
    background: #f8f7fc; font-size: .84rem; color: #4a4758;
}
.ob-modal__features li i { color: #524595; font-size: 1rem; flex-shrink: 0; }
.ob-modal__foot {
    display: flex; gap: .65rem; padding: 0 1.5rem 1.35rem; background: #fff;
}
.ob-modal__foot .btn { border-radius: .65rem; font-weight: 700; padding: .7rem 1rem; }
.ob-modal__foot .btn-primary {
    background: linear-gradient(135deg,#524595,#7b6bb8) !important; border: 0 !important;
}
</style>
