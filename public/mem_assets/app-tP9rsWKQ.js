(function () {
    function e() {
        document.getElementById("loader").classList.add("d-none");
    }
    window.addEventListener("load", e),
        [...document.querySelectorAll('[data-bs-toggle="tooltip"]')].map(
            (t) => new bootstrap.Tooltip(t)
        ),
        [...document.querySelectorAll('[data-bs-toggle="popover"]')].map(
            (t) => new bootstrap.Popover(t)
        ),
        document.addEventListener("DOMContentLoaded", function () {
            var t = document.querySelectorAll("[data-trigger]");
            for (let c = 0; c < t.length; ++c) {
                var r = t[c];
                new Choices(r, {
                    allowHTML: !0,
                    placeholderValue: "This is a placeholder set in the config",
                    searchPlaceholderValue: "Search",
                });
            }
        }),
        (document.getElementById("year").innerHTML = new Date().getFullYear()),
        Waves.attach(".btn-wave", ["waves-light"]),
        Waves.init();
    let o = ".card";
    document.querySelectorAll('[data-bs-toggle="card-remove"]').forEach((t) => {
        t.addEventListener("click", function (r) {
            return r.preventDefault(), this.closest(o).remove(), !1;
        });
    }),
        document
            .querySelectorAll('[data-bs-toggle="card-fullscreen"]')
            .forEach((t) => {
                t.addEventListener("click", function (r) {
                    let i = this.closest(o);
                    return (
                        i.classList.toggle("card-fullscreen"),
                        i.classList.remove("card-collapsed"),
                        r.preventDefault(),
                        !1
                    );
                });
            });
    var s = 1;
    setInterval(() => {
        document.querySelectorAll(".count-up").forEach((t) => {
            t.getAttribute("data-count") >= s &&
                ((s = s + 1), (t.innerText = s));
        });
    }, 10);
    const a = document.querySelector(".scrollToTop"),
        u = document.documentElement;
    (window.onscroll = () => {
        u.scrollHeight - u.clientHeight,
            window.scrollY > 100
                ? (a.style.display = "flex")
                : (a.style.display = "none");
    }),
        (a.onclick = () => {
            window.scrollTo(0, 0);
        });
    var m = document.getElementById("header-notification-scroll");
    new SimpleBar(m, { autoHide: !0 });
})();
var n = document.documentElement;
window.openFullscreen = function () {
    let e = document.querySelector(".full-screen-open"),
        l = document.querySelector(".full-screen-close");
    !document.fullscreenElement &&
    !document.webkitFullscreenElement &&
    !document.msFullscreenElement
        ? (n.requestFullscreen
              ? n.requestFullscreen()
              : n.webkitRequestFullscreen
              ? n.webkitRequestFullscreen()
              : n.msRequestFullscreen && n.msRequestFullscreen(),
          l.classList.add("d-block"),
          l.classList.remove("d-none"),
          e.classList.add("d-none"))
        : (document.exitFullscreen
              ? document.exitFullscreen()
              : document.webkitExitFullscreen
              ? document.webkitExitFullscreen()
              : document.msExitFullscreen && document.msExitFullscreen(),
          l.classList.remove("d-block"),
          e.classList.remove("d-none"),
          l.classList.add("d-none"),
          e.classList.add("d-block"));
};
let p = document.querySelectorAll(".toggle");
p.forEach((e) =>
    e.addEventListener("click", () => {
        e.classList.toggle("on");
    })
);
const g = document.querySelectorAll(".dropdown-item-close");
g.forEach((e) => {
    e.addEventListener("click", (l) => {
        if (
            (l.preventDefault(),
            l.stopPropagation(),
            e.parentNode.parentNode.parentNode.parentNode.parentNode.remove(),
            (document.getElementById("cart-data").innerText = `${
                document.querySelectorAll(".dropdown-item-close").length
            } `),
            (document.getElementById("cart-icon-badge").innerText = `${
                document.querySelectorAll(".dropdown-item-close").length
            }`),
            console.log(
                document.getElementById("header-cart-items-scroll").children
                    .length
            ),
            document.querySelectorAll(".dropdown-item-close").length == 0)
        ) {
            let d = document.querySelector(".empty-header-item"),
                o = document.querySelector(".empty-item");
            d.classList.add("d-none"), o.classList.remove("d-none");
        }
    });
});
const h = document.querySelectorAll(".dropdown-item-close1");
h.forEach((e) => {
    e.addEventListener("click", (l) => {
        if (
            (l.preventDefault(),
            l.stopPropagation(),
            e.parentNode.parentNode.parentNode.parentNode.remove(),
            (document.getElementById("notifiation-data").innerText = `${
                document.querySelectorAll(".dropdown-item-close1").length
            } Unread`),
            document.querySelectorAll(".dropdown-item-close1").length == 0)
        ) {
            let d = document.querySelector(".empty-header-item1"),
                o = document.querySelector(".empty-item1");
            d.classList.add("d-none"), o.classList.remove("d-none");
        }
    });
});
