import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Copy text to clipboard with fallback for non-HTTPS mobile/LAN environments.
 */
async function copyTextToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
        return;
    }

    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.setAttribute('readonly', '');
    document.body.appendChild(textArea);
    textArea.select();
    const ok = document.execCommand('copy');
    textArea.remove();

    if (!ok) {
        throw new Error('Clipboard copy failed');
    }
}

/**
 * Initialize code blocks with language badge and copy button.
 */
function setupCodeBlocks() {
    document.querySelectorAll('pre.code-block, pre[data-language]').forEach((pre) => {
        if (pre.parentElement && pre.parentElement.classList.contains('code-block-container')) {
            return;
        }

        const rawLang = pre.getAttribute('data-language') || 'code';
        const displayLang = rawLang.trim().toLowerCase();

        const container = document.createElement('div');
        container.className = 'code-block-container';

        const header = document.createElement('div');
        header.className = 'code-block-header';

        const langSpan = document.createElement('span');
        langSpan.className = 'code-lang';
        langSpan.textContent = displayLang;

        const copyBtn = document.createElement('button');
        copyBtn.type = 'button';
        copyBtn.className = 'code-copy-btn';
        copyBtn.setAttribute('aria-label', `Salin kode ${displayLang}`);
        copyBtn.innerHTML = `
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            </svg>
            <span>Salin</span>
        `;

        copyBtn.addEventListener('click', async () => {
            const codeEl = pre.querySelector('code') || pre;
            const textToCopy = codeEl.innerText;

            try {
                await copyTextToClipboard(textToCopy);
                const span = copyBtn.querySelector('span');
                if (span) span.textContent = 'Disalin!';

                setTimeout(() => {
                    if (span) span.textContent = 'Salin';
                }, 2000);
            } catch (err) {
                console.error('Gagal menyalin:', err);
            }
        });

        header.appendChild(langSpan);
        header.appendChild(copyBtn);

        if (pre.parentNode) {
            pre.parentNode.insertBefore(container, pre);
            container.appendChild(header);
            container.appendChild(pre);
        }
    });
}

document.addEventListener('DOMContentLoaded', setupCodeBlocks);
window.setupCodeBlocks = setupCodeBlocks;

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {
            // Ignore registration failures in unsupported or restricted contexts
        });
    });
}

Alpine.start();
