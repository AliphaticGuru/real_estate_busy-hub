<script data-theme-script>
    (function () {
        var key = 'theme';
        var root = document.documentElement;
        var media = window.matchMedia('(prefers-color-scheme: dark)');

        function get() {
            try {
                var value = window.localStorage.getItem(key);

                return value === 'light' || value === 'dark' || value === 'system' ? value : 'system';
            } catch (error) {
                // Blocked storage (private mode, policy, disabled cookies): fall back to system.
                return 'system';
            }
        }

        function apply() {
            var value = get();
            var dark = value === 'dark' || (value === 'system' && media.matches);

            root.classList.toggle('dark', dark);
            root.style.colorScheme = dark ? 'dark' : 'light';
            root.setAttribute('data-theme', value);

            window.dispatchEvent(new CustomEvent('theme-applied', { detail: { theme: value } }));
        }

        window.theme = {
            get: get,
            set: function (value) {
                var next = value === 'light' || value === 'dark' || value === 'system' ? value : 'system';

                try {
                    window.localStorage.setItem(key, next);
                } catch (error) {
                    // The in-memory preference still applies even when storage is blocked.
                }

                apply();
            },
            apply: apply,
        };

        apply();

        if (media.addEventListener) {
            media.addEventListener('change', function () {
                if (get() === 'system') {
                    apply();
                }
            });
        } else {
            media.addListener(function () {
                if (get() === 'system') {
                    apply();
                }
            });
        }

        window.addEventListener('storage', function (event) {
            if (event.key === key) {
                apply();
            }
        });

        document.addEventListener('livewire:navigated', apply);
    })();
</script>
