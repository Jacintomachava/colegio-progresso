@extends('layouts.master')

@section('conteudo')
<style>
    :root {
        --lh-green: #17a64a;
        --lh-deep: #073b32;
        --lh-blue: #087da3;
        --lh-pink: #d93a8c;
        --lh-yellow: #f7b719;
        --lh-orange: #f06f24;
        --lh-ink: #172026;
        --lh-muted: #5c6970;
        --lh-soft: #f5f8f6;
        --lh-line: #dce7df;
    }

    .lh-page {
        color: var(--lh-ink);
        font-family: "Inter", "Segoe UI", Arial, sans-serif;
        background: #ffffff;
    }

    .site-header .header-contant-block img,
    .site-header .logo-header img {
        width: auto !important;
        object-fit: contain;
    }

    .site-header .header-contant-block img {
        max-height: 92px;
    }

    .site-header .logo-header img {
        max-height: 48px;
    }

    .site-header .header-nav .dropdown-menu {
        background: var(--lh-pink) !important;
        border: 0;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 18px 35px rgba(7, 59, 50, .18);
    }

    .site-header .header-nav .dropdown-menu a {
        color: #ffffff !important;
        background: transparent !important;
        font-weight: 700;
    }

    .site-header .header-nav .dropdown-menu a:hover,
    .site-header .header-nav .dropdown-menu a:focus {
        background: rgba(7, 59, 50, .18) !important;
        color: #ffffff !important;
    }

    .lh-page h1,
    .lh-page h2,
    .lh-page h3,
    .lh-page h4,
    .lh-page p {
        margin: 0;
        letter-spacing: 0;
    }

    .lh-shell {
        width: min(1160px, calc(100% - 32px));
        margin: 0 auto;
    }

    .lh-hero {
        min-height: calc(100vh - 92px);
        display: flex;
        align-items: center;
        position: relative;
        isolation: isolate;
        background: linear-gradient(90deg, rgba(7, 59, 50, .88), rgba(7, 59, 50, .46), rgba(7, 59, 50, .12)),
            url("{{ asset('/assets/lhaysso-landing/hero-estudantes.jpeg') }}") center / cover no-repeat;
        color: #ffffff;
        overflow: hidden;
    }

    .lh-hero::after {
        content: "";
        position: absolute;
        inset: auto 0 0;
        height: 9px;
        background: linear-gradient(90deg, var(--lh-green), var(--lh-yellow), var(--lh-pink), var(--lh-blue), var(--lh-orange));
        z-index: -1;
    }

    .lh-hero-content {
        max-width: 760px;
        padding: 72px 0 96px;
    }

    .lh-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        color: #ffffff;
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .28);
        padding: 9px 13px;
        border-radius: 999px;
        margin-bottom: 20px;
    }

    .lh-kicker i {
        color: var(--lh-yellow);
    }

    .lh-hero h1 {
        font-size: clamp(42px, 7vw, 84px);
        line-height: .95;
        font-weight: 900;
        max-width: 820px;
        color: #ffffff !important;
        text-shadow: 0 18px 38px rgba(0, 0, 0, .26);
    }

    .lh-hero-lead {
        max-width: 650px;
        margin-top: 22px;
        font-size: 19px;
        line-height: 1.7;
        color: rgba(255, 255, 255, .92);
    }

    .lh-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 30px;
    }

    .lh-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 48px;
        padding: 0 20px;
        border-radius: 8px;
        font-weight: 800;
        line-height: 1;
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .lh-button:hover {
        transform: translateY(-2px);
        text-decoration: none;
    }

    .lh-button-primary {
        color: var(--lh-deep);
        background: var(--lh-yellow);
        box-shadow: 0 18px 36px rgba(0, 0, 0, .2);
    }

    .lh-button-primary:hover {
        color: var(--lh-deep);
        background: #ffd257;
    }

    .lh-button-ghost {
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, .55);
        background: rgba(255, 255, 255, .08);
    }

    .lh-button-ghost:hover {
        color: #ffffff;
        background: rgba(255, 255, 255, .16);
    }

    .lh-hero-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(120px, 1fr));
        gap: 12px;
        max-width: 640px;
        margin-top: 40px;
    }

    .lh-stat {
        border-left: 4px solid var(--lh-yellow);
        background: rgba(255, 255, 255, .14);
        backdrop-filter: blur(7px);
        padding: 14px 16px;
    }

    .lh-stat strong {
        display: block;
        font-size: 26px;
        color: #ffffff;
        line-height: 1;
    }

    .lh-stat span {
        display: block;
        margin-top: 7px;
        font-size: 13px;
        color: rgba(255, 255, 255, .82);
    }

    .lh-section {
        padding: 84px 0;
    }

    .lh-section-soft {
        background: var(--lh-soft);
    }

    .lh-section-head {
        display: grid;
        grid-template-columns: minmax(0, .86fr) minmax(280px, .54fr);
        gap: 32px;
        align-items: end;
        margin-bottom: 34px;
    }

    .lh-eyebrow {
        color: var(--lh-green);
        font-size: 13px;
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 11px;
    }

    .lh-section h2 {
        font-size: clamp(30px, 4vw, 48px);
        line-height: 1.05;
        font-weight: 900;
        color: var(--lh-deep);
    }

    .lh-section-copy {
        color: var(--lh-muted);
        font-size: 16px;
        line-height: 1.75;
    }

    .lh-feature-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .lh-services-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-top: 28px;
    }

    .lh-card {
        background: #ffffff;
        border: 1px solid var(--lh-line);
        border-radius: 8px;
        padding: 24px;
        min-height: 230px;
        box-shadow: 0 20px 45px rgba(6, 45, 37, .06);
    }

    .lh-icon {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin-bottom: 18px;
        color: #ffffff;
        font-size: 20px;
    }

    .lh-icon.green { background: var(--lh-green); }
    .lh-icon.blue { background: var(--lh-blue); }
    .lh-icon.pink { background: var(--lh-pink); }
    .lh-icon.orange { background: var(--lh-orange); }

    .lh-card h3 {
        color: var(--lh-deep);
        font-size: 20px;
        line-height: 1.25;
        font-weight: 850;
        margin-bottom: 12px;
    }

    .lh-card p {
        color: var(--lh-muted);
        font-size: 15px;
        line-height: 1.65;
    }

    .lh-service-card {
        display: grid;
        grid-template-columns: 46px 1fr;
        gap: 15px;
        align-items: start;
        min-height: 170px;
    }

    .lh-service-card .lh-icon {
        width: 46px;
        height: 46px;
        margin-bottom: 0;
        font-size: 18px;
    }

    .lh-callout-band {
        background: linear-gradient(90deg, var(--lh-green), var(--lh-blue));
        color: #ffffff;
        padding: 46px 0;
    }

    .lh-callout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 24px;
        align-items: center;
    }

    .lh-callout h2 {
        color: #ffffff;
        font-size: clamp(28px, 4vw, 44px);
        line-height: 1.08;
        font-weight: 900;
    }

    .lh-callout p {
        color: rgba(255, 255, 255, .86);
        font-size: 17px;
        line-height: 1.65;
        margin-top: 10px;
    }

    .lh-callout-mark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 132px;
        height: 132px;
        border-radius: 50%;
        color: var(--lh-deep);
        background: var(--lh-yellow);
        font-weight: 900;
        text-align: center;
        line-height: 1.15;
        padding: 20px;
        box-shadow: 0 22px 48px rgba(0, 0, 0, .18);
    }

    .lh-split {
        display: grid;
        grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr);
        gap: 44px;
        align-items: center;
    }

    .lh-photo-stack {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .lh-photo-stack img,
    .lh-wide-photo img,
    .lh-gallery img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 2.2s ease, filter 2.2s ease;
    }

    .lh-photo-tall,
    .lh-photo-small,
    .lh-wide-photo,
    .lh-gallery figure {
        position: relative;
        cursor: zoom-in;
    }

    .lh-photo-tall:hover img,
    .lh-photo-small:hover img,
    .lh-wide-photo:hover img,
    .lh-gallery figure:hover img {
        transform: scale(1.06);
        filter: brightness(1.08) saturate(1.05);
    }

    .lh-photo-tall {
        grid-row: span 2;
        min-height: 520px;
        border-radius: 8px;
        overflow: hidden;
    }

    .lh-photo-small {
        min-height: 253px;
        border-radius: 8px;
        overflow: hidden;
    }

    .lh-text-list {
        display: grid;
        gap: 18px;
        margin-top: 26px;
    }

    .lh-text-item {
        display: grid;
        grid-template-columns: 44px 1fr;
        gap: 14px;
        align-items: start;
    }

    .lh-text-item i {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e9f6ee;
        color: var(--lh-green);
        font-size: 18px;
    }

    .lh-text-item h3 {
        font-size: 18px;
        color: var(--lh-deep);
        font-weight: 850;
        margin-bottom: 5px;
    }

    .lh-text-item p {
        color: var(--lh-muted);
        line-height: 1.65;
    }

    .lh-program-band {
        background: var(--lh-deep);
        color: #ffffff;
        position: relative;
        overflow: hidden;
    }

    .lh-program-band::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(23, 166, 74, .28), transparent 48%, rgba(217, 58, 140, .20));
    }

    .lh-program-band .lh-shell {
        position: relative;
    }

    .lh-program-band .lh-eyebrow,
    .lh-program-band h2,
    .lh-program-band .lh-section-copy {
        color: #ffffff;
    }

    .lh-programs {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-top: 32px;
    }

    .lh-program {
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 8px;
        padding: 25px;
        background: rgba(255, 255, 255, .08);
    }

    .lh-program strong {
        display: block;
        color: var(--lh-yellow);
        font-size: 13px;
        text-transform: uppercase;
        margin-bottom: 12px;
    }

    .lh-program h3 {
        color: #ffffff;
        font-size: 22px;
        margin-bottom: 10px;
    }

    .lh-program p {
        color: rgba(255, 255, 255, .78);
        line-height: 1.65;
    }

    .lh-wide-photo {
        min-height: 430px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 24px 55px rgba(7, 59, 50, .16);
    }

    .lh-planetario {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(320px, .82fr);
        gap: 36px;
        align-items: center;
    }

    .lh-highlight {
        background: #ffffff;
        border: 1px solid var(--lh-line);
        border-left: 8px solid var(--lh-pink);
        border-radius: 8px;
        padding: 28px;
    }

    .lh-highlight h3 {
        color: var(--lh-deep);
        font-size: 24px;
        font-weight: 900;
        margin-bottom: 14px;
    }

    .lh-highlight p {
        color: var(--lh-muted);
        line-height: 1.72;
        margin-bottom: 14px;
    }

    .lh-checks {
        display: grid;
        gap: 10px;
        margin-top: 18px;
        padding: 0;
        list-style: none;
    }

    .lh-checks li {
        display: grid;
        grid-template-columns: 24px 1fr;
        gap: 10px;
        color: var(--lh-ink);
        line-height: 1.5;
    }

    .lh-checks i {
        color: var(--lh-green);
        margin-top: 4px;
    }

    .lh-gallery {
        display: grid;
        grid-template-columns: 1.15fr .85fr .85fr;
        grid-auto-rows: 240px;
        gap: 14px;
    }

    .lh-gallery figure {
        margin: 0;
        position: relative;
        overflow: hidden;
        border-radius: 8px;
        background: var(--lh-soft);
    }

    .lh-gallery figure:first-child {
        grid-row: span 2;
    }

    .lh-gallery figcaption {
        position: absolute;
        left: 14px;
        right: 14px;
        bottom: 14px;
        color: #ffffff;
        font-weight: 800;
        padding: 10px 12px;
        border-radius: 8px;
        background: rgba(7, 59, 50, .76);
        transition: opacity .8s ease 1.2s, transform .8s ease 1.2s;
    }

    .lh-gallery figure:hover figcaption {
        opacity: 0;
        transform: translateY(8px);
        pointer-events: none;
    }

    .lh-image-zoom {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 28px;
        background: rgba(7, 59, 50, .78);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .22s ease, visibility .22s ease;
    }

    .lh-image-zoom.is-visible {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .lh-image-zoom__frame {
        position: relative;
        width: min(1040px, 94vw);
        max-height: 88vh;
        border-radius: 8px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 34px 80px rgba(0, 0, 0, .35);
        transform: scale(.98);
        transition: transform .22s ease;
    }

    .lh-image-zoom.is-visible .lh-image-zoom__frame {
        transform: scale(1);
    }

    .lh-image-zoom__frame img {
        width: 100%;
        max-height: 88vh;
        object-fit: contain;
        display: block;
        background: #0b1d19;
    }

    .lh-image-zoom__caption {
        position: absolute;
        left: 14px;
        right: 56px;
        bottom: 14px;
        color: #ffffff;
        font-weight: 800;
        line-height: 1.35;
        padding: 10px 12px;
        border-radius: 8px;
        background: rgba(7, 59, 50, .76);
    }

    .lh-image-zoom__close {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 8px;
        background: rgba(255, 255, 255, .92);
        color: var(--lh-deep);
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
    }

    .lh-info-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-top: 30px;
    }

    .lh-info-card {
        min-height: 230px;
    }

    .lh-info-card strong {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 42px;
        height: 42px;
        border-radius: 8px;
        background: #e9f6ee;
        color: var(--lh-green);
        margin-bottom: 16px;
    }

    .lh-info-card a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 16px;
        color: var(--lh-green);
        font-weight: 900;
    }

    .lh-info-card a:hover {
        color: var(--lh-deep);
        text-decoration: none;
    }

    .lh-contact {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(320px, .82fr);
        gap: 18px;
        align-items: stretch;
    }

    .lh-contact-panel {
        background: var(--lh-deep);
        color: #ffffff;
        border-radius: 8px;
        padding: 34px;
        min-height: 100%;
    }

    .lh-contact-panel h2 {
        color: #ffffff;
        margin-bottom: 16px;
    }

    .lh-contact-panel p {
        color: rgba(255, 255, 255, .78);
        line-height: 1.7;
        margin-bottom: 24px;
    }

    .lh-contact-list {
        display: grid;
        gap: 14px;
        padding: 0;
        margin: 0;
        list-style: none;
    }

    .lh-contact-list li {
        display: grid;
        grid-template-columns: 34px 1fr;
        gap: 12px;
        color: rgba(255, 255, 255, .9);
        line-height: 1.45;
    }

    .lh-contact-list i {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .12);
        color: var(--lh-yellow);
    }

    .lh-map {
        border: 0;
        width: 100%;
        min-height: 410px;
        border-radius: 8px;
        filter: saturate(.96);
    }

    .lh-floating-whatsapp {
        position: fixed;
        right: 24px;
        bottom: 82px;
        z-index: 9998;
        width: 58px;
        height: 58px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #ffffff;
        background: #25d366;
        box-shadow: 0 18px 34px rgba(7, 59, 50, .26);
        font-size: 28px;
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .lh-floating-whatsapp:hover,
    .lh-floating-whatsapp:focus {
        color: #ffffff;
        background: #1fbd5a;
        text-decoration: none;
        transform: translateY(-3px);
        box-shadow: 0 22px 42px rgba(7, 59, 50, .32);
    }

    @media (max-width: 991px) {
        .lh-hero {
            min-height: auto;
        }

        .lh-section-head,
        .lh-split,
        .lh-planetario,
        .lh-contact {
            grid-template-columns: 1fr;
        }

        .lh-feature-grid,
        .lh-services-grid,
        .lh-info-grid,
        .lh-programs {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .lh-gallery {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 640px) {
        .site-header .header-contant-block {
            padding-top: 8px;
            padding-bottom: 8px;
        }

        .site-header .header-contant-block img {
            max-height: 78px;
        }

        .site-header .contact-info {
            display: none;
        }

        .lh-shell {
            width: min(100% - 24px, 1160px);
        }

        .lh-hero {
            background-position: 58% center;
        }

        .lh-hero-content {
            padding: 54px 0 70px;
        }

        .lh-hero-lead {
            font-size: 16px;
        }

        .lh-actions,
        .lh-hero-stats {
            display: grid;
            grid-template-columns: 1fr;
        }

        .lh-section {
            padding: 58px 0;
        }

        .lh-feature-grid,
        .lh-services-grid,
        .lh-info-grid,
        .lh-programs,
        .lh-photo-stack,
        .lh-gallery {
            grid-template-columns: 1fr;
        }

        .lh-callout {
            grid-template-columns: 1fr;
        }

        .lh-callout-mark {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .lh-card {
            min-height: auto;
        }

        .lh-photo-tall,
        .lh-photo-small,
        .lh-wide-photo,
        .lh-gallery figure,
        .lh-gallery figure:first-child {
            min-height: 260px;
            grid-row: auto;
        }

        .lh-image-zoom {
            padding: 14px;
        }

        .lh-image-zoom__caption {
            left: 10px;
            right: 10px;
            bottom: 10px;
            font-size: 13px;
        }

        .lh-floating-whatsapp {
            right: 16px;
            bottom: 78px;
            width: 52px;
            height: 52px;
            font-size: 25px;
        }
    }
</style>
<main class="lh-page">
    <section class="lh-hero" id="inicio">
        <div class="lh-shell">
            <div class="lh-hero-content">
                <div class="lh-kicker"><i class="fa fa-star"></i> Kindergarten and School</div>
                <h1>Grupo Lhaysso</h1>
                <p class="lh-hero-lead">
                    Um ambiente escolar acolhedor, colorido e organizado, onde as crianças aprendem com acompanhamento próximo,
                    actividades práticas e experiências que despertam curiosidade, autonomia e gosto pelo conhecimento.
                </p>
                <div class="lh-actions">
                    <a class="lh-button lh-button-primary" href="https://wa.me/258878403247" target="_blank" rel="noopener noreferrer">
                        <i class="fa fa-whatsapp"></i> Falar com a secretaria
                    </a>
                    <a class="lh-button lh-button-ghost" href="#sobre">
                        <i class="fa fa-angle-down"></i> Conhecer o colégio
                    </a>
                </div>
                <div class="lh-hero-stats" aria-label="Indicadores do colégio">
                    <div class="lh-stat">
                        <strong>{{ isset($total_alunos_ativos) && $total_alunos_ativos > 0 ? $total_alunos_ativos : '+' }}</strong>
                        <span>alunos activos</span>
                    </div>
                    <div class="lh-stat">
                        <strong>{{ isset($total_professores_ativos) && $total_professores_ativos > 0 ? $total_professores_ativos : '+' }}</strong>
                        <span>professores activos</span>
                    </div>
                    <div class="lh-stat">
                        <strong>Maputo</strong>
                        <span>Bairro das Mahotas</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="lh-section" id="sobre">
        <div class="lh-shell">
            <div class="lh-section-head">
                <div>
                    <p class="lh-eyebrow">Sobre o colégio</p>
                    <h2>Educação com cuidado diário e visão para o futuro.</h2>
                </div>
                <p class="lh-section-copy">
                    O Colégio Lhaysso Kindergarten and School valoriza um ensino de qualidade, com espaços preparados,
                    rotinas seguras e actividades que ligam a sala de aula ao mundo real.
                </p>
            </div>

            <div class="lh-feature-grid">
                <article class="lh-card">
                    <span class="lh-icon green"><i class="fa fa-child"></i></span>
                    <h3>Acompanhamento próximo</h3>
                    <p>Equipa pedagógica atenta ao desenvolvimento, à participação e ao bem-estar de cada criança.</p>
                </article>
                <article class="lh-card">
                    <span class="lh-icon blue"><i class="fa fa-book"></i></span>
                    <h3>Aprendizagem estruturada</h3>
                    <p>Rotinas de estudo, leitura, escrita, matemática, ciências e competências sociais desde cedo.</p>
                </article>
                <article class="lh-card">
                    <span class="lh-icon pink"><i class="fa fa-flask"></i></span>
                    <h3>Curiosidade científica</h3>
                    <p>Visitas de estudo e experiências que aproximam os alunos das Ciências Naturais e do universo.</p>
                </article>
                <article class="lh-card">
                    <span class="lh-icon orange"><i class="fa fa-shield"></i></span>
                    <h3>Ambiente organizado</h3>
                    <p>Salas, secretaria, administração e áreas de apoio pensadas para acolher famílias e alunos.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="lh-section lh-section-soft" id="servicos">
        <div class="lh-shell">
            <div class="lh-section-head">
                <div>
                    <p class="lh-eyebrow">Serviços escolares</p>
                    <h2>Formação integral, inclusão e comunicação com as famílias.</h2>
                </div>
                <p class="lh-section-copy">
                    O Colégio Lhaysso Kindergarten and School visa propiciar ao aluno formação integral, autonomia intelectual,
                    pensamento crítico e compreensão dos fundamentos científicos e técnicos através de um ensino construtivista.
                </p>
            </div>

            <div class="lh-services-grid">
                <article class="lh-card lh-service-card">
                    <span class="lh-icon pink"><i class="fa fa-heart"></i></span>
                    <div>
                        <h3>Educação especial e inclusiva</h3>
                        <p>Ensino individualizado para responder às necessidades específicas de cada aluno.</p>
                    </div>
                </article>
                <article class="lh-card lh-service-card">
                    <span class="lh-icon blue"><i class="fa fa-paint-brush"></i></span>
                    <div>
                        <h3>Actividades co-curriculares</h3>
                        <p>Actividades que fortalecem criatividade, autonomia, socialização e desenvolvimento integral.</p>
                    </div>
                </article>
                <article class="lh-card lh-service-card">
                    <span class="lh-icon green"><i class="fa fa-graduation-cap"></i></span>
                    <div>
                        <h3>Ensino de qualidade</h3>
                        <p>Professores qualificados, infra-estruturas adequadas e recursos pedagógicos.</p>
                    </div>
                </article>
                <article class="lh-card lh-service-card">
                    <span class="lh-icon orange"><i class="fa fa-commenting"></i></span>
                    <div>
                        <h3>Notificações via SMS</h3>
                        <p>Comunicação moderna para interagir com os encarregados de educação.</p>
                    </div>
                </article>
                <article class="lh-card lh-service-card">
                    <span class="lh-icon green"><i class="fa fa-book"></i></span>
                    <div>
                        <h3>1.ª à 9.ª classe</h3>
                        <p>Oferta de Ensino Básico Completo, com acompanhamento académico progressivo.</p>
                    </div>
                </article>
                <article class="lh-card lh-service-card">
                    <span class="lh-icon blue"><i class="fa fa-clock-o"></i></span>
                    <div>
                        <h3>Suporte contínuo</h3>
                        <p>Apoio e canais de contacto para aproximar a escola da comunidade.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="lh-section" id="instalacoes">
        <div class="lh-shell lh-split">
            <div class="lh-photo-stack" aria-label="Instalações do Colégio Lhaysso">
                <div class="lh-photo-tall">
                    <img src="{{ asset('/assets/lhaysso-landing/sala-colorida.jpeg') }}" alt="Sala de aula colorida do Colégio Lhaysso">
                </div>
                <div class="lh-photo-small">
                    <img src="{{ asset('/assets/lhaysso-landing/secretaria.jpeg') }}" alt="Secretaria do Colégio Lhaysso">
                </div>
                <div class="lh-photo-small">
                    <img src="{{ asset('/assets/lhaysso-landing/gabinete-pedagogico.jpeg') }}" alt="Gabinete pedagógico do colégio">
                </div>
            </div>
            <div>
                <p class="lh-eyebrow">Instalações</p>
                <h2>Espaços preparados para aprender, circular e ser bem recebido.</h2>
                <p class="lh-section-copy">
                    A escola apresenta salas equipadas, blocos administrativos e áreas de apoio que representam a
                    organização escolar.
                </p>
                <div class="lh-text-list">
                    <div class="lh-text-item">
                        <i class="fa fa-check"></i>
                        <div>
                            <h3>Salas de aula luminosas</h3>
                            <p>Carteiras adequadas, cores vivas e condições para aulas produtivas e participativas.</p>
                        </div>
                    </div>
                    <div class="lh-text-item">
                        <i class="fa fa-check"></i>
                        <div>
                            <h3>Atendimento às famílias</h3>
                            <p>Secretaria organizada para partilhar informações úteis.</p>
                        </div>
                    </div>
                    <div class="lh-text-item">
                        <i class="fa fa-check"></i>
                        <div>
                            <h3>Apoio pedagógico</h3>
                            <p>Estratégias que promovem um ambiente de aprendizagem eficaz, envolvente e inclusivo.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="lh-section lh-program-band" id="ensino">
        <div class="lh-shell">
            <div class="lh-section-head">
                <div>
                    <p class="lh-eyebrow">Oferta educativa</p>
                    <h2>Formação integral desde os primeiros anos.</h2>
                </div>
                <p class="lh-section-copy">
                    A proposta educativa combina disciplina, afeto, exploração, comunicação e experiências práticas,
                    ajudando cada aluno a crescer com confiança.
                </p>
            </div>
            <div class="lh-programs">
                <article class="lh-program">
                    <strong>Primeiros anos</strong>
                    <h3>Kindergarten</h3>
                    <p>Rotinas de socialização, linguagem, motricidade, criatividade e descoberta acompanhadas com cuidado.</p>
                </article>
                <article class="lh-program">
                    <strong>Base académica</strong>
                    <h3>Ensino primário</h3>
                    <p>Aprendizagem de literacia, numeracia e outras ciências, com acompanhamento contínuo.</p>
                </article>
                <article class="lh-program">
                    <strong>Experiências</strong>
                    <h3>Visitas de estudo</h3>
                    <p>Estratégias pedagógicas que ligam os conteúdos escolares à realidade.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="lh-section" id="planetario">
        <div class="lh-shell lh-planetario">
            <div class="lh-wide-photo">
                <img src="{{ asset('/assets/lhaysso-landing/planetario.jpeg') }}" alt="Alunos do Colégio Lhaysso numa visita ao planetário">
            </div>
            <div class="lh-highlight">
                <p class="lh-eyebrow">Aprender fora da sala</p>
                <h3>Uso do planetário: o céu nas nossas mãos.</h3>
                <p>
                    No âmbito das visitas de estudo, o colégio promove o uso do planetário para estimular a alfabetização
                    científica e aproximar os alunos das Ciências Naturais, com a finalidade de:
                </p>
                <ul class="lh-checks">
                    <li><i class="fa fa-check-circle"></i><span>Estimular a curiosidade sobre o universo.</span></li>
                    <li><i class="fa fa-check-circle"></i><span>Complementar as aulas tradicionais com recursos didácticos.</span></li>
                    <li><i class="fa fa-check-circle"></i><span>Diferenciar planetas, estrelas e satélites naturais e artificiais.</span></li>
                    <li><i class="fa fa-check-circle"></i><span>Valorizar a ciência como parte da formação da criança.</span></li>
                </ul>
            </div>
        </div>
    </section>

    <section class="lh-section lh-section-soft" id="galeria">
        <div class="lh-shell">
            <div class="lh-section-head">
                <div>
                    <p class="lh-eyebrow">Galeria</p>
                    <h2>O quotidiano do Lhaysso em imagens.</h2>
                </div>
                <p class="lh-section-copy">
                    Salas organizadas, espaços identificados, actividades pedagógicas e momentos de aprendizagem dentro e fora do colégio.
                </p>
            </div>
            <div class="lh-gallery">
                <figure>
                    <img src="{{ asset('/assets/lhaysso-landing/entrada.jpeg') }}" alt="Entrada e secretaria do Colégio Lhaysso">
                    <figcaption>Entrada e secretaria</figcaption>
                </figure>
                <figure>
                    <img src="{{ asset('/assets/lhaysso-landing/sala-azul.jpeg') }}" alt="Direcção do Colégio Lhaysso">
                    <figcaption>Administração</figcaption>
                </figure>
                <figure>
                    <img src="{{ asset('/assets/lhaysso-landing/administracao.jpeg') }}" alt="Sala de aula equipada do Colégio Lhaysso">
                    <figcaption>Salas equipadas</figcaption>
                </figure>
                <figure>
                    <img src="{{ asset('/assets/lhaysso-landing/alunos-saida.jpeg') }}" alt="Alunos do Colégio Lhaysso em actividade externa">
                    <figcaption>Actividades externas</figcaption>
                </figure>
                <figure>
                    <img src="{{ asset('/assets/lhaysso-landing/visita-estudo.jpeg') }}" alt="Alunos em visita de estudo">
                    <figcaption>Visitas de estudo</figcaption>
                </figure>
            </div>
        </div>
    </section>

    <section class="lh-callout-band" id="porque-escolher">
        <div class="lh-shell lh-callout">
            <div>
                <h2>Por que nos escolher?</h2>
                <p>
                    Venha fazer parte da nossa família educacional. No nosso colégio, cada tesouro é uma história de sucesso,
                    acompanhado com cuidado, disciplina, afeto e compromisso com a aprendizagem.
                </p>
            </div>
            <div class="lh-callout-mark">Meu filho, meu tesouro</div>
        </div>
    </section>

    <section class="lh-section lh-section-soft" id="informacoes">
        <div class="lh-shell">
            <div class="lh-section-head">
                <div>
                    <p class="lh-eyebrow">Informações úteis</p>
                    <h2>Orientação rápida para pais e encarregados de educação.</h2>
                </div>
                <p class="lh-section-copy">
                    Reunimos aqui os pontos mais procurados por quem quer conhecer melhor o colégio, preparar a matrícula
                    ou acompanhar a vida escolar dos alunos.
                </p>
            </div>

            <div class="lh-info-grid">
                <article class="lh-card lh-info-card" id="calendario">
                    <strong><i class="fa fa-calendar"></i></strong>
                    <h3>Calendário escolar</h3>
                    <p>As datas de actividades, avaliações, reuniões e eventos são comunicadas pela secretaria e pelos canais oficiais do colégio.</p>
                    <a href="{{ url('/') }}#contacto">Consultar secretaria <i class="fa fa-arrow-right"></i></a>
                </article>
                <article class="lh-card lh-info-card" id="quadro-honra">
                    <strong><i class="fa fa-trophy"></i></strong>
                    <h3>Quadro de honra</h3>
                    <p>O colégio valoriza empenho, disciplina, participação e evolução dos alunos, reconhecendo boas práticas ao longo do ano lectivo.</p>
                    <a href="{{ url('/') }}#ensino">Ver proposta educativa <i class="fa fa-arrow-right"></i></a>
                </article>
                <article class="lh-card lh-info-card" id="admissao">
                    <strong><i class="fa fa-user-plus"></i></strong>
                    <h3>Admissão</h3>
                    <p>Famílias interessadas podem solicitar informação sobre vagas, documentos necessários e etapas de admissão directamente à secretaria.</p>
                    <a href="https://wa.me/258878403247" target="_blank" rel="noopener noreferrer">Pedir informação <i class="fa fa-arrow-right"></i></a>
                </article>
                <article class="lh-card lh-info-card" id="matricula-info">
                    <strong><i class="fa fa-file-text-o"></i></strong>
                    <h3>Matrícula e propina</h3>
                    <p>A equipa administrativa orienta os encarregados sobre matrícula, renovação, propinas e formas de acompanhamento financeiro.</p>
                    <a href="{{ url('/') }}#contacto">Ver contactos <i class="fa fa-arrow-right"></i></a>
                </article>
                <article class="lh-card lh-info-card" id="regulamento">
                    <strong><i class="fa fa-shield"></i></strong>
                    <h3>Regulamento interno</h3>
                    <p>As normas de convivência, segurança, assiduidade e comunicação ajudam a manter um ambiente escolar organizado e acolhedor.</p>
                    <a href="{{ route('termos') }}">Consultar termos <i class="fa fa-arrow-right"></i></a>
                </article>
                <article class="lh-card lh-info-card" id="politicas">
                    <strong><i class="fa fa-lock"></i></strong>
                    <h3>Políticas e privacidade</h3>
                    <p>As políticas públicas do sistema escolar estão disponíveis para consulta pelos encarregados e utilizadores da plataforma.</p>
                    <a href="{{ route('politicas') }}">Ver política <i class="fa fa-arrow-right"></i></a>
                </article>
            </div>
        </div>
    </section>

    <section class="lh-section" id="contacto">
        <div class="lh-shell lh-contact">
            <div class="lh-contact-panel">
                <p class="lh-eyebrow">Contactos</p>
                <h2>Visite-nos ou fale com a secretaria.</h2>
                <p>
                    Para informações sobre admissões, matrículas, propinas ou visitas ao colégio, entre em contacto pelos canais oficiais.
                </p>
                <ul class="lh-contact-list">
                    <li><i class="fa fa-map-marker"></i><span>Av. Cardeal Dom Alexandre, Bloco 2, Quarteirão n.º 22, Distrito Municipal KaMavota, Bairro das Mahotas, Cidade de Maputo.</span></li>
                    <li><i class="fa fa-phone"></i><span>+258 878403247</span></li>
                    <li><i class="fa fa-whatsapp"></i><span><a href="https://wa.me/258878403247" target="_blank" rel="noopener noreferrer" style="color: #ffffff;">WhatsApp 878403247</a></span></li>
                    <li><i class="fa fa-envelope"></i><span>lhayssokkindergarden@gmail.com</span></li>
                    <li><i class="fa fa-clock-o"></i><span>Segunda a sexta-feira, das 08h00 às 17h00</span></li>
                </ul>
            </div>
            <iframe
                class="lh-map"
                title="Mapa do Colégio Lhaysso Kindergarten and School"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps?q=Col%C3%A9gio%20Lhaysso%20Kindergarten%20and%20School%2C%20Maputo&output=embed">
            </iframe>
        </div>
    </section>

    <div class="lh-image-zoom" id="lhImageZoom" aria-hidden="true">
        <div class="lh-image-zoom__frame" role="dialog" aria-modal="true" aria-label="Imagem ampliada">
            <button class="lh-image-zoom__close" type="button" aria-label="Fechar imagem ampliada">&times;</button>
            <img src="" alt="">
            <div class="lh-image-zoom__caption"></div>
        </div>
    </div>

    <a class="lh-floating-whatsapp" href="https://wa.me/258878403247" target="_blank" rel="noopener noreferrer" aria-label="Falar pelo WhatsApp">
        <i class="fa fa-whatsapp" aria-hidden="true"></i>
    </a>
</main>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var zoom = document.getElementById('lhImageZoom');
        if (!zoom) {
            return;
        }

        var zoomImg = zoom.querySelector('img');
        var caption = zoom.querySelector('.lh-image-zoom__caption');
        var close = zoom.querySelector('.lh-image-zoom__close');
        var timer = null;
        var activeImage = null;
        var dwellMs = 950;
        var selector = '.lh-photo-stack img, .lh-wide-photo img, .lh-gallery img';

        function imageLabel(img) {
            var figureCaption = img.closest('figure') ? img.closest('figure').querySelector('figcaption') : null;
            return (figureCaption && figureCaption.textContent.trim()) || img.getAttribute('alt') || 'Imagem do Colégio Lhaysso';
        }

        function clearZoomTimer() {
            if (timer) {
                window.clearTimeout(timer);
                timer = null;
            }
        }

        function openZoom(img) {
            activeImage = img;
            zoomImg.src = img.currentSrc || img.src;
            zoomImg.alt = img.alt || 'Imagem ampliada';
            caption.textContent = imageLabel(img);
            zoom.classList.add('is-visible');
            zoom.setAttribute('aria-hidden', 'false');
        }

        function closeZoom() {
            clearZoomTimer();
            zoom.classList.remove('is-visible');
            zoom.setAttribute('aria-hidden', 'true');
            activeImage = null;
        }

        function scheduleZoom(img) {
            clearZoomTimer();
            timer = window.setTimeout(function () {
                openZoom(img);
            }, dwellMs);
        }

        document.querySelectorAll(selector).forEach(function (img) {
            img.addEventListener('mouseenter', function () {
                scheduleZoom(img);
            });

            img.addEventListener('mousemove', function () {
                if (!zoom.classList.contains('is-visible')) {
                    scheduleZoom(img);
                }
            });

            img.addEventListener('mouseleave', function () {
                if (!zoom.classList.contains('is-visible')) {
                    clearZoomTimer();
                }
            });
        });

        close.addEventListener('click', closeZoom);
        zoom.addEventListener('click', function (event) {
            if (event.target === zoom) {
                closeZoom();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeZoom();
            }
        });
    });
</script>
@endsection
