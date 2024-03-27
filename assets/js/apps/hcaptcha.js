(() => {
    "use strict";
    const t = function (t) {
        return "string" != typeof t || "" === t ? (console.error("The namespace must be a non-empty string."), !1) : !!/^[a-zA-Z][a-zA-Z0-9_.\-\/]*$/.test(t) || (console.error("The namespace can only contain numbers, letters, dashes, periods, underscores and slashes."), !1)
    };
    const e = function (t) {
        return "string" != typeof t || "" === t ? (console.error("The hook name must be a non-empty string."), !1) : /^__/.test(t) ? (console.error("The hook name cannot begin with `__`."), !1) : !!/^[a-zA-Z][a-zA-Z0-9_.-]*$/.test(t) || (console.error("The hook name can only contain numbers, letters, dashes, periods and underscores."), !1)
    };
    const n = function (n, r) {
        return function (i, o, a, s = 10) {
            const u = n[r];
            if (!e(i)) return;
            if (!t(o)) return;
            if ("function" != typeof a) return void console.error("The hook callback must be a function.");
            if ("number" != typeof s) return void console.error("If specified, the hook priority must be a number.");
            const c = {callback: a, priority: s, namespace: o};
            if (u[i]) {
                const t = u[i].handlers;
                let e;
                for (e = t.length; e > 0 && !(s >= t[e - 1].priority); e--) ;
                e === t.length ? t[e] = c : t.splice(e, 0, c), u.__current.forEach((t => {
                    t.name === i && t.currentIndex >= e && t.currentIndex++
                }))
            } else u[i] = {handlers: [c], runs: 0};
            "hookAdded" !== i && n.doAction("hookAdded", i, o, a, s)
        }
    };
    const r = function (n, r, i = !1) {
        return function (o, a) {
            const s = n[r];
            if (!e(o)) return;
            if (!i && !t(a)) return;
            if (!s[o]) return 0;
            let u = 0;
            if (i) u = s[o].handlers.length, s[o] = {runs: s[o].runs, handlers: []}; else {
                const t = s[o].handlers;
                for (let e = t.length - 1; e >= 0; e--) t[e].namespace === a && (t.splice(e, 1), u++, s.__current.forEach((t => {
                    t.name === o && t.currentIndex >= e && t.currentIndex--
                })))
            }
            return "hookRemoved" !== o && n.doAction("hookRemoved", o, a), u
        }
    };
    const i = function (t, e) {
        return function (n, r) {
            const i = t[e];
            return void 0 !== r ? n in i && i[n].handlers.some((t => t.namespace === r)) : n in i
        }
    };
    const o = function (t, e, n = !1) {
        return function (r, ...i) {
            const o = t[e];
            o[r] || (o[r] = {handlers: [], runs: 0}), o[r].runs++;
            const a = o[r].handlers;
            if (!a || !a.length) return n ? i[0] : void 0;
            const s = {name: r, currentIndex: 0};
            for (o.__current.push(s); s.currentIndex < a.length;) {
                const t = a[s.currentIndex].callback.apply(null, i);
                n && (i[0] = t), s.currentIndex++
            }
            return o.__current.pop(), n ? i[0] : void 0
        }
    };
    const a = function (t, e) {
        return function () {
            var n;
            const r = t[e];
            return null !== (n = r.__current[r.__current.length - 1]?.name) && void 0 !== n ? n : null
        }
    };
    const s = function (t, e) {
        return function (n) {
            const r = t[e];
            return void 0 === n ? void 0 !== r.__current[0] : !!r.__current[0] && n === r.__current[0].name
        }
    };
    const u = function (t, n) {
        return function (r) {
            const i = t[n];
            if (e(r)) return i[r] && i[r].runs ? i[r].runs : 0
        }
    };

    class c {
        constructor() {
            this.actions = Object.create(null), this.actions.__current = [], this.filters = Object.create(null), this.filters.__current = [], this.addAction = n(this, "actions"), this.addFilter = n(this, "filters"), this.removeAction = r(this, "actions"), this.removeFilter = r(this, "filters"), this.hasAction = i(this, "actions"), this.hasFilter = i(this, "filters"), this.removeAllActions = r(this, "actions", !0), this.removeAllFilters = r(this, "filters", !0), this.doAction = o(this, "actions"), this.applyFilters = o(this, "filters", !0), this.currentAction = a(this, "actions"), this.currentFilter = a(this, "filters"), this.doingAction = s(this, "actions"), this.doingFilter = s(this, "filters"), this.didAction = u(this, "actions"), this.didFilter = u(this, "filters")
        }
    }

    const l = function () {
        return new c
    }, d = l(), {
        addAction: h,
        addFilter: f,
        removeAction: m,
        removeFilter: v,
        hasAction: p,
        hasFilter: b,
        removeAllActions: y,
        removeAllFilters: k,
        doAction: g,
        applyFilters: w,
        currentAction: A,
        currentFilter: E,
        doingAction: _,
        doingFilter: S,
        didAction: I,
        didFilter: C,
        actions: F,
        filters: O
    } = d;

    function T(t) {
        return T = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (t) {
            return typeof t
        } : function (t) {
            return t && "function" == typeof Symbol && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t
        }, T(t)
    }

    function B(t) {
        return function (t) {
            if (Array.isArray(t)) return D(t)
        }(t) || function (t) {
            if ("undefined" != typeof Symbol && null != t[Symbol.iterator] || null != t["@@iterator"]) return Array.from(t)
        }(t) || j(t) || function () {
            throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")
        }()
    }

    function j(t, e) {
        if (t) {
            if ("string" == typeof t) return D(t, e);
            var n = Object.prototype.toString.call(t).slice(8, -1);
            return "Object" === n && t.constructor && (n = t.constructor.name), "Map" === n || "Set" === n ? Array.from(t) : "Arguments" === n || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n) ? D(t, e) : void 0
        }
    }

    function D(t, e) {
        (null == e || e > t.length) && (e = t.length);
        for (var n = 0, r = new Array(e); n < e; n++) r[n] = t[n];
        return r
    }

    function x(t, e) {
        for (var n = 0; n < e.length; n++) {
            var r = e[n];
            r.enumerable = r.enumerable || !1, r.configurable = !0, "value" in r && (r.writable = !0), Object.defineProperty(t, (i = r.key, o = void 0, o = function (t, e) {
                if ("object" !== T(t) || null === t) return t;
                var n = t[Symbol.toPrimitive];
                if (void 0 !== n) {
                    var r = n.call(t, e || "default");
                    if ("object" !== T(r)) return r;
                    throw new TypeError("@@toPrimitive must return a primitive value.")
                }
                return ("string" === e ? String : Number)(t)
            }(i, "string"), "symbol" === T(o) ? o : String(o)), r)
        }
        var i, o
    }

    var P = new (function () {
        function t() {
            !function (t, e) {
                if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
            }(this, t), this.formSelector = "form, div.fl-login-form, section.cwginstock-subscribe-form, div.sdm_download_item, .gform_editor, #nf-builder", this.submitButtonSelector = '*[type="submit"]:not(.quform-default-submit):not(.nf-element), #check_config, a.fl-button span, button[type="button"].ff-btn, a.et_pb_newsletter_button.et_pb_button, .forminator-button-submit, .frm_button_submit, a.sdm_download', this.foundForms = [], this.params = null, this.observing = !1, this.darkElement = null, this.darkClass = null, this.hooks = l(), this.callback = this.callback.bind(this), this.validate = this.validate.bind(this)
        }

        var e, n, r;
        return e = t, (n = [{
            key: "generateID", value: function () {
                var t = function () {
                    return Math.floor(65536 * (1 + Math.random())).toString(16).substring(1)
                };
                return t() + "-" + t() + "-" + t() + "-" + t()
            }
        }, {
            key: "getFoundFormById", value: function (t) {
                return this.foundForms.filter((function (e) {
                    return t === e.procaptchaId
                }))[0]
            }
        }, {
            key: "getWidgetId", value: function (t) {
                return t.getElementsByClassName("h-captcha")[0].getElementsByTagName("iframe")[0].dataset.procaptchaWidgetId
            }
        }, {
            key: "reset", value: function (t) {
                procaptcha.reset(this.getWidgetId(t))
            }
        }, {
            key: "isSameOrDescendant", value: function (t, e) {
                for (var n = e; n;) {
                    if (n === t) return !0;
                    n = n.parentElement
                }
                return !1
            }
        }, {
            key: "validate", value: function (t) {
                var e = t.currentTarget.closest(this.formSelector),
                    n = this.getFoundFormById(e.dataset.procaptchaId).submitButtonElement;
                this.isSameOrDescendant(n, t.target) && (t.preventDefault(), t.stopPropagation(), this.currentForm = {
                    formElement: e,
                    submitButtonElement: n
                }, procaptcha.execute(this.getWidgetId(e)))
            }
        }, {
            key: "getForms", value: function () {
                return B(document.querySelectorAll(this.formSelector))
            }
        }, {
            key: "getParams", value: function () {
                if (null !== this.params) return this.params;
                var t;
                try {
                    t = JSON.parse(ProcaptchaMainObject.params)
                } catch (e) {
                    t = {}
                }
                return t.callback = this.callback, t
            }
        }, {
            key: "setParams", value: function (t) {
                this.params = t
            }
        }, {
            key: "setDarkData", value: function () {
                var t = {
                    "twenty-twenty-one": {
                        darkStyleId: "twenty-twenty-one-style-css",
                        darkElement: document.body,
                        darkClass: "is-dark-theme"
                    },
                    "wp-dark-mode": {
                        darkStyleId: "wp-dark-mode-frontend-css",
                        darkElement: document.documentElement,
                        darkClass: "wp-dark-mode-active"
                    },
                    "droit-dark-mode": {
                        darkStyleId: "dtdr-public-inline-css",
                        darkElement: document.documentElement,
                        darkClass: "drdt-dark-mode"
                    }
                };
                t = this.hooks.applyFilters("procaptcha.darkData", t);
                for (var e = 0, n = Object.values(t); e < n.length; e++) {
                    var r = n[e];
                    if (document.getElementById(r.darkStyleId)) return this.darkElement = r.darkElement, void (this.darkClass = r.darkClass)
                }
            }
        }, {
            key: "observeDarkMode", value: function () {
                var t = this;
                this.observing || (this.observing = !0, "auto" !== this.getParams().theme) || (this.setDarkData(), this.darkElement && this.darkClass && new MutationObserver((function (e) {
                    var n, r = function (t, e) {
                        var n = "undefined" != typeof Symbol && t[Symbol.iterator] || t["@@iterator"];
                        if (!n) {
                            if (Array.isArray(t) || (n = j(t)) || e && t && "number" == typeof t.length) {
                                n && (t = n);
                                var r = 0, i = function () {
                                };
                                return {
                                    s: i, n: function () {
                                        return r >= t.length ? {done: !0} : {done: !1, value: t[r++]}
                                    }, e: function (t) {
                                        throw t
                                    }, f: i
                                }
                            }
                            throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")
                        }
                        var o, a = !0, s = !1;
                        return {
                            s: function () {
                                n = n.call(t)
                            }, n: function () {
                                var t = n.next();
                                return a = t.done, t
                            }, e: function (t) {
                                s = !0, o = t
                            }, f: function () {
                                try {
                                    a || null == n.return || n.return()
                                } finally {
                                    if (s) throw o
                                }
                            }
                        }
                    }(e);
                    try {
                        var i = function () {
                            var e = n.value.oldValue, r = t.darkElement.getAttribute("class");
                            e = e ? e.split(" ") : [], (r = r ? r.split(" ") : []).filter((function (t) {
                                return !e.includes(t)
                            })).concat(e.filter((function (t) {
                                return !r.includes(t)
                            }))).includes(t.darkClass) && t.bindEvents()
                        };
                        for (r.s(); !(n = r.n()).done;) i()
                    } catch (t) {
                        r.e(t)
                    } finally {
                        r.f()
                    }
                })).observe(this.darkElement, {attributes: !0, attributeOldValue: !0}))
            }
        }, {
            key: "callback", value: function (t) {
                document.dispatchEvent(new CustomEvent("procaptchaSubmitted", {detail: {token: t}})), "invisible" === this.getParams().size && this.submit()
            }
        }, {
            key: "applyAutoTheme", value: function (t) {
                if ("auto" !== t.theme) return t;
                if (t.theme = "light", !this.darkElement) return t;
                var e = this.darkElement.getAttribute("class");
                return (e = e || "").includes(this.darkClass) && (t.theme = "dark"), t
            }
        }, {
            key: "render", value: function (t) {
                this.observeDarkMode();
                var e = this.applyAutoTheme(this.getParams());
                procaptcha.render(t, e)
            }
        }, {
            key: "bindEvents", value: function () {
                var t = this;
                "undefined" != typeof procaptcha && this.getForms().map((function (e) {
                    var n = e.querySelector(".h-captcha");
                    if (null === n) return e;
                    if (n.classList.contains("procaptcha-widget-id")) return e;
                    var r = n.querySelector("iframe");
                    if (null !== r && r.remove(), t.render(n), "invisible" !== n.dataset.size) return e;
                    var i = e.querySelectorAll(t.submitButtonSelector)[0];
                    if (!i) return e;
                    var o = t.generateID();
                    return t.foundForms.push({
                        procaptchaId: o,
                        submitButtonElement: i
                    }), e.dataset.procaptchaId = o, i.addEventListener("click", t.validate, !0), e
                }))
            }
        }, {
            key: "submit", value: function () {
                var t = this.currentForm.formElement, e = this.currentForm.submitButtonElement,
                    n = e.getAttribute("type");
                if (n = n ? n.toLowerCase() : "", "form" !== t.tagName.toLowerCase() || "submit" !== n) return e.removeEventListener("click", this.validate, !0), void e.click();
                t.requestSubmit ? t.requestSubmit(e) : t.submit()
            }
        }]) && x(e.prototype, n), r && x(e, r), Object.defineProperty(e, "prototype", {writable: !1}), t
    }());
    window.procaptcha = P, window.procaptchaGetWidgetId = function (t) {
        P.getWidgetId(t)
    }, window.procaptchaReset = function (t) {
        P.reset(t)
    }, window.procaptchaBindEvents = function () {
        P.bindEvents()
    }, window.procaptchaSubmit = function () {
        P.submit()
    }, window.procaptchaOnLoad = function () {
        window.procaptchaBindEvents(), document.dispatchEvent(new CustomEvent("procaptchaLoaded"))
    }
})();
