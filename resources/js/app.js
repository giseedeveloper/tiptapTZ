import './bootstrap';
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

const renderLucideIcons = () => {
    if (document.body?.dataset.landingPage !== 'true') {
        return;
    }

    if (!document.querySelector('[data-lucide]')) {
        return;
    }

    createIcons({ icons: landingIcons });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderLucideIcons, { once: true });
} else {
    renderLucideIcons();
}

window.renderLucideIcons = renderLucideIcons;
