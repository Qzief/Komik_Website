(() => {
    const STORAGE_KEY = "komikhub-reader-chapter-order";

    const initReaderMobile = () => {
        const shell = document.querySelector("[data-reader-shell]");
        if (!shell) {
            return;
        }

        const controls = shell.querySelector("[data-reader-mobile-controls]");
        const controlsBar = shell.querySelector("[data-reader-controls-bar]");
        const collapseButton = shell.querySelector("[data-reader-controls-collapse]");
        const picker = shell.querySelector("[data-reader-picker]");
        const readerImages = shell.querySelector(".reader-images");
        const chromeNodes = shell.querySelectorAll(".reader-mobile-chrome");
        const pickerOpenButtons = shell.querySelectorAll("[data-reader-picker-open]");
        const pickerCloseButtons = shell.querySelectorAll("[data-reader-picker-close]");
        const chapterList = shell.querySelector("[data-reader-chapter-list]");
        const orderButtons = shell.querySelectorAll("[data-reader-order-button]");

        if (!controls || !controlsBar || !collapseButton || !picker || !chapterList || orderButtons.length === 0 || !readerImages) {
            return;
        }

        const chapterItems = Array.from(chapterList.querySelectorAll("[data-reader-chapter-item]"));

        const applyOrder = (order) => {
            const sorted = [...chapterItems].sort((left, right) => {
                const leftNumber = Number(left.dataset.chapterNumber || "0");
                const rightNumber = Number(right.dataset.chapterNumber || "0");

                return order === "asc"
                    ? leftNumber - rightNumber
                    : rightNumber - leftNumber;
            });

            sorted.forEach((item) => chapterList.appendChild(item));

            orderButtons.forEach((button) => {
                button.classList.toggle("is-active", button.dataset.readerOrderButton === order);
            });

            window.localStorage.setItem(STORAGE_KEY, order);
        };

        const openPicker = () => {
            expandControls();
            picker.hidden = false;
            document.body.classList.add("reader-picker-open");
        };

        const closePicker = () => {
            picker.hidden = true;
            document.body.classList.remove("reader-picker-open");
        };

        const collapseControls = () => {
            shell.classList.add("is-chrome-hidden");
        };

        const expandControls = () => {
            shell.classList.remove("is-chrome-hidden");
        };

        const toggleControls = () => {
            if (picker.hidden === false) {
                return;
            }

            shell.classList.toggle("is-chrome-hidden");
        };

        const savedOrder = window.localStorage.getItem(STORAGE_KEY);
        applyOrder(savedOrder === "asc" ? "asc" : "desc");
        expandControls();

        collapseButton?.addEventListener("click", collapseControls);

        pickerOpenButtons.forEach((button) => {
            button.addEventListener("click", openPicker);
        });

        pickerCloseButtons.forEach((button) => {
            button.addEventListener("click", closePicker);
        });

        orderButtons.forEach((button) => {
            button.addEventListener("click", () => {
                const nextOrder = button.dataset.readerOrderButton === "asc" ? "asc" : "desc";
                applyOrder(nextOrder);
            });
        });

        chromeNodes.forEach((node) => {
            node.addEventListener("click", (event) => {
                event.stopPropagation();
            });
        });

        picker.addEventListener("click", (event) => {
            event.stopPropagation();
        });

        readerImages.addEventListener("click", () => {
            if (window.innerWidth <= 760) {
                toggleControls();
            }
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                closePicker();
                expandControls();
            }
        });

        window.addEventListener("resize", () => {
            if (window.innerWidth > 760) {
                closePicker();
                expandControls();
            }
        });
    };

    document.addEventListener("DOMContentLoaded", initReaderMobile);
})();
