<style>
    .analysis-hero {
        background: linear-gradient(135deg, rgba(140, 113, 246, 0.18) 0%, rgba(236, 72, 153, 0.08) 40%, rgba(15, 10, 30, 0.95) 100%);
        border: 1px solid rgba(140, 113, 246, 0.28);
    }
    .analysis-filter-bar {
        backdrop-filter: blur(12px);
        background: rgba(18, 16, 28, 0.85);
    }
    .analysis-hub-card {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.06) 0%, rgba(18, 16, 28, 0.92) 100%);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .analysis-hub-card:hover {
        transform: translateY(-3px);
        border-color: rgba(140, 113, 246, 0.45);
        box-shadow: 0 16px 40px -16px rgba(140, 113, 246, 0.45);
    }
    .analysis-hub-pill {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.12);
        transition: all 0.2s ease;
    }
    .analysis-hub-pill:hover {
        background: linear-gradient(135deg, rgba(140, 113, 246, 0.35), rgba(109, 82, 232, 0.2));
        border-color: rgba(140, 113, 246, 0.5);
        color: #fff;
    }
    .journey-pipeline { display: flex; align-items: stretch; gap: 0; padding: 0.25rem 0; }
    @media (max-width: 1023px) { .journey-pipeline { flex-direction: column; align-items: center; } }
    .journey-step { flex: 1 1 0; min-width: 9.5rem; max-width: 11rem; position: relative; }
    @media (max-width: 1023px) { .journey-step { min-width: 100%; max-width: 22rem; width: 100%; } }
    .journey-step-card {
        background: linear-gradient(160deg, rgba(109, 82, 232, 0.22) 0%, rgba(18, 16, 28, 0.95) 100%);
        border: 1px solid rgba(140, 113, 246, 0.35);
        border-radius: 1rem;
        padding: 1rem 0.85rem;
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .journey-step-card:hover { transform: translateY(-2px); box-shadow: 0 12px 32px -12px rgba(140, 113, 246, 0.45); }
    .journey-step-card.is-weakest { border-color: rgba(245, 158, 11, 0.55); }
    .journey-step-card.is-success { border-color: rgba(16, 185, 129, 0.45); background: linear-gradient(160deg, rgba(16, 185, 129, 0.15) 0%, rgba(18, 16, 28, 0.95) 100%); }
    .journey-retention-track { height: 6px; border-radius: 9999px; background: rgba(255, 255, 255, 0.08); overflow: hidden; margin-top: 0.65rem; }
    .journey-retention-fill { height: 100%; border-radius: 9999px; background: linear-gradient(90deg, #5B3FD6, #8C71F6); transition: width 0.6s cubic-bezier(0.34, 1.56, 0.64, 1); }
    .journey-connector { display: flex; flex-direction: column; align-items: center; justify-content: center; flex: 0 0 auto; width: 2.75rem; padding: 0.5rem 0; }
    @media (max-width: 1023px) { .journey-connector { width: 100%; max-width: 22rem; height: 2.5rem; flex-direction: row; gap: 0.5rem; } }
    .journey-connector-line { flex: 1; width: 2px; min-height: 1.25rem; background: linear-gradient(180deg, rgba(140, 113, 246, 0.5), rgba(140, 113, 246, 0.15)); }
    @media (max-width: 1023px) { .journey-connector-line { width: auto; height: 2px; min-height: 0; flex: 1; background: linear-gradient(90deg, rgba(140, 113, 246, 0.5), rgba(140, 113, 246, 0.15)); } }
    .journey-drop-badge { font-size: 9px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.45rem; border-radius: 9999px; background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.35); color: #fda4af; white-space: nowrap; }
    .journey-drop-badge.is-none { background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.3); color: #6ee7b7; }
    @keyframes analysis-shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
    .analysis-skeleton { background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%); background-size: 200% 100%; animation: analysis-shimmer 1.4s infinite; }
    .insight-card-positive { border-color: rgba(16, 185, 129, 0.35); background: rgba(16, 185, 129, 0.08); }
    .insight-card-warning { border-color: rgba(245, 158, 11, 0.35); background: rgba(245, 158, 11, 0.08); }
    .insight-card-alert { border-color: rgba(244, 63, 94, 0.35); background: rgba(244, 63, 94, 0.08); }
    .insight-card-info { border-color: rgba(140, 113, 246, 0.35); background: rgba(140, 113, 246, 0.08); }
</style>
