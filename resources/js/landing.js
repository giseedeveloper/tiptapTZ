import {
    ArrowRight,
    Banknote,
    BookOpen,
    Bot,
    CalendarClock,
    Check,
    CheckCircle2,
    ChevronDown,
    Mail,
    MapPin,
    Menu,
    MessageCircle,
    MessagesSquare,
    Mic,
    Monitor,
    Play,
    Plus,
    QrCode,
    Receipt,
    Send,
    ShieldCheck,
    Store,
    TrendingUp,
    User,
    UtensilsCrossed,
    X,
    createIcons,
} from 'lucide';

const landingIcons = {
    ArrowRight,
    Banknote,
    BookOpen,
    Bot,
    CalendarClock,
    Check,
    CheckCircle2,
    ChevronDown,
    Mail,
    MapPin,
    Menu,
    MessageCircle,
    MessagesSquare,
    Mic,
    Monitor,
    Play,
    Plus,
    QrCode,
    Receipt,
    Send,
    ShieldCheck,
    Store,
    TrendingUp,
    User,
    UtensilsCrossed,
    X,
};

const bootLanding = () => {
    if (document.querySelector('[data-lucide]')) {
        createIcons({ icons: landingIcons });
    }

    const nav = document.getElementById('site-nav');
    window.addEventListener('scroll', () => nav?.classList.toggle('scrolled', window.scrollY > 24), { passive: true });

    const menuBtn = document.getElementById('mobile-menu-btn');
    const menuClose = document.getElementById('mobile-menu-close');
    const menu = document.getElementById('mobile-menu');
    menuBtn?.addEventListener('click', () => { menu?.classList.replace('hidden', 'flex'); });
    menuClose?.addEventListener('click', () => { menu?.classList.replace('flex', 'hidden'); });

    if ('IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        document.querySelectorAll('.reveal').forEach((element) => revealObserver.observe(element));
    } else {
        document.querySelectorAll('.reveal').forEach((element) => element.classList.add('visible'));
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootLanding, { once: true });
} else {
    bootLanding();
}
