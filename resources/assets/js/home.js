!function (e) {
    var t = {};

    function n(i) {
        if (t[i]) return t[i].exports;
        var o = t[i] = {i: i, l: !1, exports: {}};
        return e[i].call(o.exports, o, o.exports, n), o.l = !0, o.exports
    }

    n.m = e, n.c = t, n.d = function (e, t, i) {
        n.o(e, t) || Object.defineProperty(e, t, {enumerable: !0, get: i})
    }, n.r = function (e) {
        "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e, Symbol.toStringTag, {value: "Module"}), Object.defineProperty(e, "__esModule", {value: !0})
    }, n.t = function (e, t) {
        if (1 & t && (e = n(e)), 8 & t) return e;
        if (4 & t && "object" == typeof e && e && e.__esModule) return e;
        var i = Object.create(null);
        if (n.r(i), Object.defineProperty(i, "default", {
            enumerable: !0,
            value: e
        }), 2 & t && "string" != typeof e) for (var o in e) n.d(i, o, function (t) {
            return e[t]
        }.bind(null, o));
        return i
    }, n.n = function (e) {
        var t = e && e.__esModule ? function () {
            return e.default
        } : function () {
            return e
        };
        return n.d(t, "a", t), t
    }, n.o = function (e, t) {
        return Object.prototype.hasOwnProperty.call(e, t)
    }, n.p = "", n(n.s = 0)
}([function (e, t, n) {
    n(1), n(12), n(14), n(15), n(17), n(20), n(49), n(52), n(54), n(56), n(59), n(61), n(63)
}, function (e, t, n) {
    n(2), n(10), n(11)
}, function (e, t, n) {
    window.$ = window.jQuery = n(3), window.Popper = n(4), n(6), window.SmoothScroll = n(8), n(9), function (e, t) {
        var n = {
            name: "TheSaaS",
            version: "2.1.1",
            vendors: [],
            body: e("body"),
            navbar: e(".navbar"),
            header: e(".header"),
            footer: e(".footer"),
            defaults: {
                googleApiKey: null,
                googleAnalyticsKey: null,
                reCaptchaSiteKey: null,
                reCaptchaLanguage: null,
                disableAOSonMobile: !0,
                smoothScroll: !1
            },
            init: function () {
                n.initVendors(), n.initBind(), n.initDrawer(), n.initFont(), n.initForm(), n.initMailer(), n.initModal(), n.initNavbar(), n.initOffcanvas(), n.initPopup(), n.initScroll(), n.initSection(), n.initSidebar(), n.initVideo(), e(document).on("click", ".switch", function () {
                    var t = e(this).children(".switch-input").not(":disabled");
                    t.prop("checked", !t.prop("checked"))
                }), e('[data-provide="anchor"]').each(function () {
                    var t = e(this);
                    t.append('<a class="anchor" href="#' + t.attr("id") + '"></a>')
                })
            },
            initVendors: function () {
                n.vendors.forEach(function (e) {
                    var n = t.page["init" + e];
                    "function" == typeof n && n()
                })
            },
            registerVendor: function (e) {
                n.vendors.push(e)
            }
        };
        t.page = n
    }(jQuery, window), $(function () {
    })
}, function (e, t, n) {
    var i;
    /*!
 * jQuery JavaScript Library v3.3.1
 * https://jquery.com/
 *
 * Includes Sizzle.js
 * https://sizzlejs.com/
 *
 * Copyright JS Foundation and other contributors
 * Released under the MIT license
 * https://jquery.org/license
 *
 * Date: 2018-01-20T17:24Z
 */
    /*!
 * jQuery JavaScript Library v3.3.1
 * https://jquery.com/
 *
 * Includes Sizzle.js
 * https://sizzlejs.com/
 *
 * Copyright JS Foundation and other contributors
 * Released under the MIT license
 * https://jquery.org/license
 *
 * Date: 2018-01-20T17:24Z
 */
    !function (t, n) {
        "use strict";
        "object" == typeof e && "object" == typeof e.exports ? e.exports = t.document ? n(t, !0) : function (e) {
            if (!e.document) throw new Error("jQuery requires a window with a document");
            return n(e)
        } : n(t)
    }("undefined" != typeof window ? window : this, function (n, o) {
        "use strict";
        var r = [], s = n.document, a = Object.getPrototypeOf, l = r.slice, c = r.concat, u = r.push, d = r.indexOf,
            p = {}, f = p.toString, h = p.hasOwnProperty, m = h.toString, g = m.call(Object), v = {}, y = function (e) {
                return "function" == typeof e && "number" != typeof e.nodeType
            }, b = function (e) {
                return null != e && e === e.window
            }, w = {type: !0, src: !0, noModule: !0};

        function x(e, t, n) {
            var i, o = (t = t || s).createElement("script");
            if (o.text = e, n) for (i in w) n[i] && (o[i] = n[i]);
            t.head.appendChild(o).parentNode.removeChild(o)
        }

        function T(e) {
            return null == e ? e + "" : "object" == typeof e || "function" == typeof e ? p[f.call(e)] || "object" : typeof e
        }

        var S = function (e, t) {
            return new S.fn.init(e, t)
        }, C = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;

        function E(e) {
            var t = !!e && "length" in e && e.length, n = T(e);
            return !y(e) && !b(e) && ("array" === n || 0 === t || "number" == typeof t && t > 0 && t - 1 in e)
        }

        S.fn = S.prototype = {
            jquery: "3.3.1", constructor: S, length: 0, toArray: function () {
                return l.call(this)
            }, get: function (e) {
                return null == e ? l.call(this) : e < 0 ? this[e + this.length] : this[e]
            }, pushStack: function (e) {
                var t = S.merge(this.constructor(), e);
                return t.prevObject = this, t
            }, each: function (e) {
                return S.each(this, e)
            }, map: function (e) {
                return this.pushStack(S.map(this, function (t, n) {
                    return e.call(t, n, t)
                }))
            }, slice: function () {
                return this.pushStack(l.apply(this, arguments))
            }, first: function () {
                return this.eq(0)
            }, last: function () {
                return this.eq(-1)
            }, eq: function (e) {
                var t = this.length, n = +e + (e < 0 ? t : 0);
                return this.pushStack(n >= 0 && n < t ? [this[n]] : [])
            }, end: function () {
                return this.prevObject || this.constructor()
            }, push: u, sort: r.sort, splice: r.splice
        }, S.extend = S.fn.extend = function () {
            var e, t, n, i, o, r, s = arguments[0] || {}, a = 1, l = arguments.length, c = !1;
            for ("boolean" == typeof s && (c = s, s = arguments[a] || {}, a++), "object" == typeof s || y(s) || (s = {}), a === l && (s = this, a--); a < l; a++) if (null != (e = arguments[a])) for (t in e) n = s[t], s !== (i = e[t]) && (c && i && (S.isPlainObject(i) || (o = Array.isArray(i))) ? (o ? (o = !1, r = n && Array.isArray(n) ? n : []) : r = n && S.isPlainObject(n) ? n : {}, s[t] = S.extend(c, r, i)) : void 0 !== i && (s[t] = i));
            return s
        }, S.extend({
            expando: "jQuery" + ("3.3.1" + Math.random()).replace(/\D/g, ""), isReady: !0, error: function (e) {
                throw new Error(e)
            }, noop: function () {
            }, isPlainObject: function (e) {
                var t, n;
                return !(!e || "[object Object]" !== f.call(e)) && (!(t = a(e)) || "function" == typeof (n = h.call(t, "constructor") && t.constructor) && m.call(n) === g)
            }, isEmptyObject: function (e) {
                var t;
                for (t in e) return !1;
                return !0
            }, globalEval: function (e) {
                x(e)
            }, each: function (e, t) {
                var n, i = 0;
                if (E(e)) for (n = e.length; i < n && !1 !== t.call(e[i], i, e[i]); i++) ; else for (i in e) if (!1 === t.call(e[i], i, e[i])) break;
                return e
            }, trim: function (e) {
                return null == e ? "" : (e + "").replace(C, "")
            }, makeArray: function (e, t) {
                var n = t || [];
                return null != e && (E(Object(e)) ? S.merge(n, "string" == typeof e ? [e] : e) : u.call(n, e)), n
            }, inArray: function (e, t, n) {
                return null == t ? -1 : d.call(t, e, n)
            }, merge: function (e, t) {
                for (var n = +t.length, i = 0, o = e.length; i < n; i++) e[o++] = t[i];
                return e.length = o, e
            }, grep: function (e, t, n) {
                for (var i = [], o = 0, r = e.length, s = !n; o < r; o++) !t(e[o], o) !== s && i.push(e[o]);
                return i
            }, map: function (e, t, n) {
                var i, o, r = 0, s = [];
                if (E(e)) for (i = e.length; r < i; r++) null != (o = t(e[r], r, n)) && s.push(o); else for (r in e) null != (o = t(e[r], r, n)) && s.push(o);
                return c.apply([], s)
            }, guid: 1, support: v
        }), "function" == typeof Symbol && (S.fn[Symbol.iterator] = r[Symbol.iterator]), S.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "), function (e, t) {
            p["[object " + t + "]"] = t.toLowerCase()
        });
        var k =
            /*!
 * Sizzle CSS Selector Engine v2.3.3
 * https://sizzlejs.com/
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license
 * http://jquery.org/license
 *
 * Date: 2016-08-08
 */
            function (e) {
                var t, n, i, o, r, s, a, l, c, u, d, p, f, h, m, g, v, y, b, w = "sizzle" + 1 * new Date,
                    x = e.document, T = 0, S = 0, C = se(), E = se(), k = se(), _ = function (e, t) {
                        return e === t && (d = !0), 0
                    }, A = {}.hasOwnProperty, I = [], O = I.pop, D = I.push, L = I.push, N = I.slice, P = function (e, t) {
                        for (var n = 0, i = e.length; n < i; n++) if (e[n] === t) return n;
                        return -1
                    },
                    M = "checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",
                    j = "[\\x20\\t\\r\\n\\f]", H = "(?:\\\\.|[\\w-]|[^\0-\\xa0])+",
                    $ = "\\[" + j + "*(" + H + ")(?:" + j + "*([*^$|!~]?=)" + j + "*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|(" + H + "))|)" + j + "*\\]",
                    F = ":(" + H + ")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|" + $ + ")*)|.*)\\)|)",
                    R = new RegExp(j + "+", "g"),
                    W = new RegExp("^" + j + "+|((?:^|[^\\\\])(?:\\\\.)*)" + j + "+$", "g"),
                    V = new RegExp("^" + j + "*," + j + "*"), z = new RegExp("^" + j + "*([>+~]|" + j + ")" + j + "*"),
                    q = new RegExp("=" + j + "*([^\\]'\"]*?)" + j + "*\\]", "g"), B = new RegExp(F),
                    U = new RegExp("^" + H + "$"), G = {
                        ID: new RegExp("^#(" + H + ")"),
                        CLASS: new RegExp("^\\.(" + H + ")"),
                        TAG: new RegExp("^(" + H + "|[*])"),
                        ATTR: new RegExp("^" + $),
                        PSEUDO: new RegExp("^" + F),
                        CHILD: new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(" + j + "*(even|odd|(([+-]|)(\\d*)n|)" + j + "*(?:([+-]|)" + j + "*(\\d+)|))" + j + "*\\)|)", "i"),
                        bool: new RegExp("^(?:" + M + ")$", "i"),
                        needsContext: new RegExp("^" + j + "*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\(" + j + "*((?:-\\d)?\\d*)" + j + "*\\)|)(?=[^-]|$)", "i")
                    }, Y = /^(?:input|select|textarea|button)$/i, K = /^h\d$/i, Q = /^[^{]+\{\s*\[native \w/,
                    Z = /^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/, X = /[+~]/,
                    J = new RegExp("\\\\([\\da-f]{1,6}" + j + "?|(" + j + ")|.)", "ig"), ee = function (e, t, n) {
                        var i = "0x" + t - 65536;
                        return i != i || n ? t : i < 0 ? String.fromCharCode(i + 65536) : String.fromCharCode(i >> 10 | 55296, 1023 & i | 56320)
                    }, te = /([\0-\x1f\x7f]|^-?\d)|^-$|[^\0-\x1f\x7f-\uFFFF\w-]/g, ne = function (e, t) {
                        return t ? "\0" === e ? "�" : e.slice(0, -1) + "\\" + e.charCodeAt(e.length - 1).toString(16) + " " : "\\" + e
                    }, ie = function () {
                        p()
                    }, oe = ye(function (e) {
                        return !0 === e.disabled && ("form" in e || "label" in e)
                    }, {dir: "parentNode", next: "legend"});
                try {
                    L.apply(I = N.call(x.childNodes), x.childNodes), I[x.childNodes.length].nodeType
                } catch (e) {
                    L = {
                        apply: I.length ? function (e, t) {
                            D.apply(e, N.call(t))
                        } : function (e, t) {
                            for (var n = e.length, i = 0; e[n++] = t[i++];) ;
                            e.length = n - 1
                        }
                    }
                }

                function re(e, t, i, o) {
                    var r, a, c, u, d, h, v, y = t && t.ownerDocument, T = t ? t.nodeType : 9;
                    if (i = i || [], "string" != typeof e || !e || 1 !== T && 9 !== T && 11 !== T) return i;
                    if (!o && ((t ? t.ownerDocument || t : x) !== f && p(t), t = t || f, m)) {
                        if (11 !== T && (d = Z.exec(e))) if (r = d[1]) {
                            if (9 === T) {
                                if (!(c = t.getElementById(r))) return i;
                                if (c.id === r) return i.push(c), i
                            } else if (y && (c = y.getElementById(r)) && b(t, c) && c.id === r) return i.push(c), i
                        } else {
                            if (d[2]) return L.apply(i, t.getElementsByTagName(e)), i;
                            if ((r = d[3]) && n.getElementsByClassName && t.getElementsByClassName) return L.apply(i, t.getElementsByClassName(r)), i
                        }
                        if (n.qsa && !k[e + " "] && (!g || !g.test(e))) {
                            if (1 !== T) y = t, v = e; else if ("object" !== t.nodeName.toLowerCase()) {
                                for ((u = t.getAttribute("id")) ? u = u.replace(te, ne) : t.setAttribute("id", u = w), a = (h = s(e)).length; a--;) h[a] = "#" + u + " " + ve(h[a]);
                                v = h.join(","), y = X.test(e) && me(t.parentNode) || t
                            }
                            if (v) try {
                                return L.apply(i, y.querySelectorAll(v)), i
                            } catch (e) {
                            } finally {
                                u === w && t.removeAttribute("id")
                            }
                        }
                    }
                    return l(e.replace(W, "$1"), t, i, o)
                }

                function se() {
                    var e = [];
                    return function t(n, o) {
                        return e.push(n + " ") > i.cacheLength && delete t[e.shift()], t[n + " "] = o
                    }
                }

                function ae(e) {
                    return e[w] = !0, e
                }

                function le(e) {
                    var t = f.createElement("fieldset");
                    try {
                        return !!e(t)
                    } catch (e) {
                        return !1
                    } finally {
                        t.parentNode && t.parentNode.removeChild(t), t = null
                    }
                }

                function ce(e, t) {
                    for (var n = e.split("|"), o = n.length; o--;) i.attrHandle[n[o]] = t
                }

                function ue(e, t) {
                    var n = t && e, i = n && 1 === e.nodeType && 1 === t.nodeType && e.sourceIndex - t.sourceIndex;
                    if (i) return i;
                    if (n) for (; n = n.nextSibling;) if (n === t) return -1;
                    return e ? 1 : -1
                }

                function de(e) {
                    return function (t) {
                        return "input" === t.nodeName.toLowerCase() && t.type === e
                    }
                }

                function pe(e) {
                    return function (t) {
                        var n = t.nodeName.toLowerCase();
                        return ("input" === n || "button" === n) && t.type === e
                    }
                }

                function fe(e) {
                    return function (t) {
                        return "form" in t ? t.parentNode && !1 === t.disabled ? "label" in t ? "label" in t.parentNode ? t.parentNode.disabled === e : t.disabled === e : t.isDisabled === e || t.isDisabled !== !e && oe(t) === e : t.disabled === e : "label" in t && t.disabled === e
                    }
                }

                function he(e) {
                    return ae(function (t) {
                        return t = +t, ae(function (n, i) {
                            for (var o, r = e([], n.length, t), s = r.length; s--;) n[o = r[s]] && (n[o] = !(i[o] = n[o]))
                        })
                    })
                }

                function me(e) {
                    return e && void 0 !== e.getElementsByTagName && e
                }

                for (t in n = re.support = {}, r = re.isXML = function (e) {
                    var t = e && (e.ownerDocument || e).documentElement;
                    return !!t && "HTML" !== t.nodeName
                }, p = re.setDocument = function (e) {
                    var t, o, s = e ? e.ownerDocument || e : x;
                    return s !== f && 9 === s.nodeType && s.documentElement ? (h = (f = s).documentElement, m = !r(f), x !== f && (o = f.defaultView) && o.top !== o && (o.addEventListener ? o.addEventListener("unload", ie, !1) : o.attachEvent && o.attachEvent("onunload", ie)), n.attributes = le(function (e) {
                        return e.className = "i", !e.getAttribute("className")
                    }), n.getElementsByTagName = le(function (e) {
                        return e.appendChild(f.createComment("")), !e.getElementsByTagName("*").length
                    }), n.getElementsByClassName = Q.test(f.getElementsByClassName), n.getById = le(function (e) {
                        return h.appendChild(e).id = w, !f.getElementsByName || !f.getElementsByName(w).length
                    }), n.getById ? (i.filter.ID = function (e) {
                        var t = e.replace(J, ee);
                        return function (e) {
                            return e.getAttribute("id") === t
                        }
                    }, i.find.ID = function (e, t) {
                        if (void 0 !== t.getElementById && m) {
                            var n = t.getElementById(e);
                            return n ? [n] : []
                        }
                    }) : (i.filter.ID = function (e) {
                        var t = e.replace(J, ee);
                        return function (e) {
                            var n = void 0 !== e.getAttributeNode && e.getAttributeNode("id");
                            return n && n.value === t
                        }
                    }, i.find.ID = function (e, t) {
                        if (void 0 !== t.getElementById && m) {
                            var n, i, o, r = t.getElementById(e);
                            if (r) {
                                if ((n = r.getAttributeNode("id")) && n.value === e) return [r];
                                for (o = t.getElementsByName(e), i = 0; r = o[i++];) if ((n = r.getAttributeNode("id")) && n.value === e) return [r]
                            }
                            return []
                        }
                    }), i.find.TAG = n.getElementsByTagName ? function (e, t) {
                        return void 0 !== t.getElementsByTagName ? t.getElementsByTagName(e) : n.qsa ? t.querySelectorAll(e) : void 0
                    } : function (e, t) {
                        var n, i = [], o = 0, r = t.getElementsByTagName(e);
                        if ("*" === e) {
                            for (; n = r[o++];) 1 === n.nodeType && i.push(n);
                            return i
                        }
                        return r
                    }, i.find.CLASS = n.getElementsByClassName && function (e, t) {
                        if (void 0 !== t.getElementsByClassName && m) return t.getElementsByClassName(e)
                    }, v = [], g = [], (n.qsa = Q.test(f.querySelectorAll)) && (le(function (e) {
                        h.appendChild(e).innerHTML = "<a id='" + w + "'></a><select id='" + w + "-\r\\' msallowcapture=''><option selected=''></option></select>", e.querySelectorAll("[msallowcapture^='']").length && g.push("[*^$]=" + j + "*(?:''|\"\")"), e.querySelectorAll("[selected]").length || g.push("\\[" + j + "*(?:value|" + M + ")"), e.querySelectorAll("[id~=" + w + "-]").length || g.push("~="), e.querySelectorAll(":checked").length || g.push(":checked"), e.querySelectorAll("a#" + w + "+*").length || g.push(".#.+[+~]")
                    }), le(function (e) {
                        e.innerHTML = "<a href='' disabled='disabled'></a><select disabled='disabled'><option/></select>";
                        var t = f.createElement("input");
                        t.setAttribute("type", "hidden"), e.appendChild(t).setAttribute("name", "D"), e.querySelectorAll("[name=d]").length && g.push("name" + j + "*[*^$|!~]?="), 2 !== e.querySelectorAll(":enabled").length && g.push(":enabled", ":disabled"), h.appendChild(e).disabled = !0, 2 !== e.querySelectorAll(":disabled").length && g.push(":enabled", ":disabled"), e.querySelectorAll("*,:x"), g.push(",.*:")
                    })), (n.matchesSelector = Q.test(y = h.matches || h.webkitMatchesSelector || h.mozMatchesSelector || h.oMatchesSelector || h.msMatchesSelector)) && le(function (e) {
                        n.disconnectedMatch = y.call(e, "*"), y.call(e, "[s!='']:x"), v.push("!=", F)
                    }), g = g.length && new RegExp(g.join("|")), v = v.length && new RegExp(v.join("|")), t = Q.test(h.compareDocumentPosition), b = t || Q.test(h.contains) ? function (e, t) {
                        var n = 9 === e.nodeType ? e.documentElement : e, i = t && t.parentNode;
                        return e === i || !(!i || 1 !== i.nodeType || !(n.contains ? n.contains(i) : e.compareDocumentPosition && 16 & e.compareDocumentPosition(i)))
                    } : function (e, t) {
                        if (t) for (; t = t.parentNode;) if (t === e) return !0;
                        return !1
                    }, _ = t ? function (e, t) {
                        if (e === t) return d = !0, 0;
                        var i = !e.compareDocumentPosition - !t.compareDocumentPosition;
                        return i || (1 & (i = (e.ownerDocument || e) === (t.ownerDocument || t) ? e.compareDocumentPosition(t) : 1) || !n.sortDetached && t.compareDocumentPosition(e) === i ? e === f || e.ownerDocument === x && b(x, e) ? -1 : t === f || t.ownerDocument === x && b(x, t) ? 1 : u ? P(u, e) - P(u, t) : 0 : 4 & i ? -1 : 1)
                    } : function (e, t) {
                        if (e === t) return d = !0, 0;
                        var n, i = 0, o = e.parentNode, r = t.parentNode, s = [e], a = [t];
                        if (!o || !r) return e === f ? -1 : t === f ? 1 : o ? -1 : r ? 1 : u ? P(u, e) - P(u, t) : 0;
                        if (o === r) return ue(e, t);
                        for (n = e; n = n.parentNode;) s.unshift(n);
                        for (n = t; n = n.parentNode;) a.unshift(n);
                        for (; s[i] === a[i];) i++;
                        return i ? ue(s[i], a[i]) : s[i] === x ? -1 : a[i] === x ? 1 : 0
                    }, f) : f
                }, re.matches = function (e, t) {
                    return re(e, null, null, t)
                }, re.matchesSelector = function (e, t) {
                    if ((e.ownerDocument || e) !== f && p(e), t = t.replace(q, "='$1']"), n.matchesSelector && m && !k[t + " "] && (!v || !v.test(t)) && (!g || !g.test(t))) try {
                        var i = y.call(e, t);
                        if (i || n.disconnectedMatch || e.document && 11 !== e.document.nodeType) return i
                    } catch (e) {
                    }
                    return re(t, f, null, [e]).length > 0
                }, re.contains = function (e, t) {
                    return (e.ownerDocument || e) !== f && p(e), b(e, t)
                }, re.attr = function (e, t) {
                    (e.ownerDocument || e) !== f && p(e);
                    var o = i.attrHandle[t.toLowerCase()],
                        r = o && A.call(i.attrHandle, t.toLowerCase()) ? o(e, t, !m) : void 0;
                    return void 0 !== r ? r : n.attributes || !m ? e.getAttribute(t) : (r = e.getAttributeNode(t)) && r.specified ? r.value : null
                }, re.escape = function (e) {
                    return (e + "").replace(te, ne)
                }, re.error = function (e) {
                    throw new Error("Syntax error, unrecognized expression: " + e)
                }, re.uniqueSort = function (e) {
                    var t, i = [], o = 0, r = 0;
                    if (d = !n.detectDuplicates, u = !n.sortStable && e.slice(0), e.sort(_), d) {
                        for (; t = e[r++];) t === e[r] && (o = i.push(r));
                        for (; o--;) e.splice(i[o], 1)
                    }
                    return u = null, e
                }, o = re.getText = function (e) {
                    var t, n = "", i = 0, r = e.nodeType;
                    if (r) {
                        if (1 === r || 9 === r || 11 === r) {
                            if ("string" == typeof e.textContent) return e.textContent;
                            for (e = e.firstChild; e; e = e.nextSibling) n += o(e)
                        } else if (3 === r || 4 === r) return e.nodeValue
                    } else for (; t = e[i++];) n += o(t);
                    return n
                }, (i = re.selectors = {
                    cacheLength: 50,
                    createPseudo: ae,
                    match: G,
                    attrHandle: {},
                    find: {},
                    relative: {
                        ">": {dir: "parentNode", first: !0},
                        " ": {dir: "parentNode"},
                        "+": {dir: "previousSibling", first: !0},
                        "~": {dir: "previousSibling"}
                    },
                    preFilter: {
                        ATTR: function (e) {
                            return e[1] = e[1].replace(J, ee), e[3] = (e[3] || e[4] || e[5] || "").replace(J, ee), "~=" === e[2] && (e[3] = " " + e[3] + " "), e.slice(0, 4)
                        }, CHILD: function (e) {
                            return e[1] = e[1].toLowerCase(), "nth" === e[1].slice(0, 3) ? (e[3] || re.error(e[0]), e[4] = +(e[4] ? e[5] + (e[6] || 1) : 2 * ("even" === e[3] || "odd" === e[3])), e[5] = +(e[7] + e[8] || "odd" === e[3])) : e[3] && re.error(e[0]), e
                        }, PSEUDO: function (e) {
                            var t, n = !e[6] && e[2];
                            return G.CHILD.test(e[0]) ? null : (e[3] ? e[2] = e[4] || e[5] || "" : n && B.test(n) && (t = s(n, !0)) && (t = n.indexOf(")", n.length - t) - n.length) && (e[0] = e[0].slice(0, t), e[2] = n.slice(0, t)), e.slice(0, 3))
                        }
                    },
                    filter: {
                        TAG: function (e) {
                            var t = e.replace(J, ee).toLowerCase();
                            return "*" === e ? function () {
                                return !0
                            } : function (e) {
                                return e.nodeName && e.nodeName.toLowerCase() === t
                            }
                        }, CLASS: function (e) {
                            var t = C[e + " "];
                            return t || (t = new RegExp("(^|" + j + ")" + e + "(" + j + "|$)")) && C(e, function (e) {
                                return t.test("string" == typeof e.className && e.className || void 0 !== e.getAttribute && e.getAttribute("class") || "")
                            })
                        }, ATTR: function (e, t, n) {
                            return function (i) {
                                var o = re.attr(i, e);
                                return null == o ? "!=" === t : !t || (o += "", "=" === t ? o === n : "!=" === t ? o !== n : "^=" === t ? n && 0 === o.indexOf(n) : "*=" === t ? n && o.indexOf(n) > -1 : "$=" === t ? n && o.slice(-n.length) === n : "~=" === t ? (" " + o.replace(R, " ") + " ").indexOf(n) > -1 : "|=" === t && (o === n || o.slice(0, n.length + 1) === n + "-"))
                            }
                        }, CHILD: function (e, t, n, i, o) {
                            var r = "nth" !== e.slice(0, 3), s = "last" !== e.slice(-4), a = "of-type" === t;
                            return 1 === i && 0 === o ? function (e) {
                                return !!e.parentNode
                            } : function (t, n, l) {
                                var c, u, d, p, f, h, m = r !== s ? "nextSibling" : "previousSibling", g = t.parentNode,
                                    v = a && t.nodeName.toLowerCase(), y = !l && !a, b = !1;
                                if (g) {
                                    if (r) {
                                        for (; m;) {
                                            for (p = t; p = p[m];) if (a ? p.nodeName.toLowerCase() === v : 1 === p.nodeType) return !1;
                                            h = m = "only" === e && !h && "nextSibling"
                                        }
                                        return !0
                                    }
                                    if (h = [s ? g.firstChild : g.lastChild], s && y) {
                                        for (b = (f = (c = (u = (d = (p = g)[w] || (p[w] = {}))[p.uniqueID] || (d[p.uniqueID] = {}))[e] || [])[0] === T && c[1]) && c[2], p = f && g.childNodes[f]; p = ++f && p && p[m] || (b = f = 0) || h.pop();) if (1 === p.nodeType && ++b && p === t) {
                                            u[e] = [T, f, b];
                                            break
                                        }
                                    } else if (y && (b = f = (c = (u = (d = (p = t)[w] || (p[w] = {}))[p.uniqueID] || (d[p.uniqueID] = {}))[e] || [])[0] === T && c[1]), !1 === b) for (; (p = ++f && p && p[m] || (b = f = 0) || h.pop()) && ((a ? p.nodeName.toLowerCase() !== v : 1 !== p.nodeType) || !++b || (y && ((u = (d = p[w] || (p[w] = {}))[p.uniqueID] || (d[p.uniqueID] = {}))[e] = [T, b]), p !== t));) ;
                                    return (b -= o) === i || b % i == 0 && b / i >= 0
                                }
                            }
                        }, PSEUDO: function (e, t) {
                            var n,
                                o = i.pseudos[e] || i.setFilters[e.toLowerCase()] || re.error("unsupported pseudo: " + e);
                            return o[w] ? o(t) : o.length > 1 ? (n = [e, e, "", t], i.setFilters.hasOwnProperty(e.toLowerCase()) ? ae(function (e, n) {
                                for (var i, r = o(e, t), s = r.length; s--;) e[i = P(e, r[s])] = !(n[i] = r[s])
                            }) : function (e) {
                                return o(e, 0, n)
                            }) : o
                        }
                    },
                    pseudos: {
                        not: ae(function (e) {
                            var t = [], n = [], i = a(e.replace(W, "$1"));
                            return i[w] ? ae(function (e, t, n, o) {
                                for (var r, s = i(e, null, o, []), a = e.length; a--;) (r = s[a]) && (e[a] = !(t[a] = r))
                            }) : function (e, o, r) {
                                return t[0] = e, i(t, null, r, n), t[0] = null, !n.pop()
                            }
                        }), has: ae(function (e) {
                            return function (t) {
                                return re(e, t).length > 0
                            }
                        }), contains: ae(function (e) {
                            return e = e.replace(J, ee), function (t) {
                                return (t.textContent || t.innerText || o(t)).indexOf(e) > -1
                            }
                        }), lang: ae(function (e) {
                            return U.test(e || "") || re.error("unsupported lang: " + e), e = e.replace(J, ee).toLowerCase(), function (t) {
                                var n;
                                do {
                                    if (n = m ? t.lang : t.getAttribute("xml:lang") || t.getAttribute("lang")) return (n = n.toLowerCase()) === e || 0 === n.indexOf(e + "-")
                                } while ((t = t.parentNode) && 1 === t.nodeType);
                                return !1
                            }
                        }), target: function (t) {
                            var n = e.location && e.location.hash;
                            return n && n.slice(1) === t.id
                        }, root: function (e) {
                            return e === h
                        }, focus: function (e) {
                            return e === f.activeElement && (!f.hasFocus || f.hasFocus()) && !!(e.type || e.href || ~e.tabIndex)
                        }, enabled: fe(!1), disabled: fe(!0), checked: function (e) {
                            var t = e.nodeName.toLowerCase();
                            return "input" === t && !!e.checked || "option" === t && !!e.selected
                        }, selected: function (e) {
                            return e.parentNode && e.parentNode.selectedIndex, !0 === e.selected
                        }, empty: function (e) {
                            for (e = e.firstChild; e; e = e.nextSibling) if (e.nodeType < 6) return !1;
                            return !0
                        }, parent: function (e) {
                            return !i.pseudos.empty(e)
                        }, header: function (e) {
                            return K.test(e.nodeName)
                        }, input: function (e) {
                            return Y.test(e.nodeName)
                        }, button: function (e) {
                            var t = e.nodeName.toLowerCase();
                            return "input" === t && "button" === e.type || "button" === t
                        }, text: function (e) {
                            var t;
                            return "input" === e.nodeName.toLowerCase() && "text" === e.type && (null == (t = e.getAttribute("type")) || "text" === t.toLowerCase())
                        }, first: he(function () {
                            return [0]
                        }), last: he(function (e, t) {
                            return [t - 1]
                        }), eq: he(function (e, t, n) {
                            return [n < 0 ? n + t : n]
                        }), even: he(function (e, t) {
                            for (var n = 0; n < t; n += 2) e.push(n);
                            return e
                        }), odd: he(function (e, t) {
                            for (var n = 1; n < t; n += 2) e.push(n);
                            return e
                        }), lt: he(function (e, t, n) {
                            for (var i = n < 0 ? n + t : n; --i >= 0;) e.push(i);
                            return e
                        }), gt: he(function (e, t, n) {
                            for (var i = n < 0 ? n + t : n; ++i < t;) e.push(i);
                            return e
                        })
                    }
                }).pseudos.nth = i.pseudos.eq, {
                    radio: !0,
                    checkbox: !0,
                    file: !0,
                    password: !0,
                    image: !0
                }) i.pseudos[t] = de(t);
                for (t in {submit: !0, reset: !0}) i.pseudos[t] = pe(t);

                function ge() {
                }

                function ve(e) {
                    for (var t = 0, n = e.length, i = ""; t < n; t++) i += e[t].value;
                    return i
                }

                function ye(e, t, n) {
                    var i = t.dir, o = t.next, r = o || i, s = n && "parentNode" === r, a = S++;
                    return t.first ? function (t, n, o) {
                        for (; t = t[i];) if (1 === t.nodeType || s) return e(t, n, o);
                        return !1
                    } : function (t, n, l) {
                        var c, u, d, p = [T, a];
                        if (l) {
                            for (; t = t[i];) if ((1 === t.nodeType || s) && e(t, n, l)) return !0
                        } else for (; t = t[i];) if (1 === t.nodeType || s) if (u = (d = t[w] || (t[w] = {}))[t.uniqueID] || (d[t.uniqueID] = {}), o && o === t.nodeName.toLowerCase()) t = t[i] || t; else {
                            if ((c = u[r]) && c[0] === T && c[1] === a) return p[2] = c[2];
                            if (u[r] = p, p[2] = e(t, n, l)) return !0
                        }
                        return !1
                    }
                }

                function be(e) {
                    return e.length > 1 ? function (t, n, i) {
                        for (var o = e.length; o--;) if (!e[o](t, n, i)) return !1;
                        return !0
                    } : e[0]
                }

                function we(e, t, n, i, o) {
                    for (var r, s = [], a = 0, l = e.length, c = null != t; a < l; a++) (r = e[a]) && (n && !n(r, i, o) || (s.push(r), c && t.push(a)));
                    return s
                }

                function xe(e, t, n, i, o, r) {
                    return i && !i[w] && (i = xe(i)), o && !o[w] && (o = xe(o, r)), ae(function (r, s, a, l) {
                        var c, u, d, p = [], f = [], h = s.length, m = r || function (e, t, n) {
                                for (var i = 0, o = t.length; i < o; i++) re(e, t[i], n);
                                return n
                            }(t || "*", a.nodeType ? [a] : a, []), g = !e || !r && t ? m : we(m, p, e, a, l),
                            v = n ? o || (r ? e : h || i) ? [] : s : g;
                        if (n && n(g, v, a, l), i) for (c = we(v, f), i(c, [], a, l), u = c.length; u--;) (d = c[u]) && (v[f[u]] = !(g[f[u]] = d));
                        if (r) {
                            if (o || e) {
                                if (o) {
                                    for (c = [], u = v.length; u--;) (d = v[u]) && c.push(g[u] = d);
                                    o(null, v = [], c, l)
                                }
                                for (u = v.length; u--;) (d = v[u]) && (c = o ? P(r, d) : p[u]) > -1 && (r[c] = !(s[c] = d))
                            }
                        } else v = we(v === s ? v.splice(h, v.length) : v), o ? o(null, s, v, l) : L.apply(s, v)
                    })
                }

                function Te(e) {
                    for (var t, n, o, r = e.length, s = i.relative[e[0].type], a = s || i.relative[" "], l = s ? 1 : 0, u = ye(function (e) {
                        return e === t
                    }, a, !0), d = ye(function (e) {
                        return P(t, e) > -1
                    }, a, !0), p = [function (e, n, i) {
                        var o = !s && (i || n !== c) || ((t = n).nodeType ? u(e, n, i) : d(e, n, i));
                        return t = null, o
                    }]; l < r; l++) if (n = i.relative[e[l].type]) p = [ye(be(p), n)]; else {
                        if ((n = i.filter[e[l].type].apply(null, e[l].matches))[w]) {
                            for (o = ++l; o < r && !i.relative[e[o].type]; o++) ;
                            return xe(l > 1 && be(p), l > 1 && ve(e.slice(0, l - 1).concat({value: " " === e[l - 2].type ? "*" : ""})).replace(W, "$1"), n, l < o && Te(e.slice(l, o)), o < r && Te(e = e.slice(o)), o < r && ve(e))
                        }
                        p.push(n)
                    }
                    return be(p)
                }

                return ge.prototype = i.filters = i.pseudos, i.setFilters = new ge, s = re.tokenize = function (e, t) {
                    var n, o, r, s, a, l, c, u = E[e + " "];
                    if (u) return t ? 0 : u.slice(0);
                    for (a = e, l = [], c = i.preFilter; a;) {
                        for (s in n && !(o = V.exec(a)) || (o && (a = a.slice(o[0].length) || a), l.push(r = [])), n = !1, (o = z.exec(a)) && (n = o.shift(), r.push({
                            value: n,
                            type: o[0].replace(W, " ")
                        }), a = a.slice(n.length)), i.filter) !(o = G[s].exec(a)) || c[s] && !(o = c[s](o)) || (n = o.shift(), r.push({
                            value: n,
                            type: s,
                            matches: o
                        }), a = a.slice(n.length));
                        if (!n) break
                    }
                    return t ? a.length : a ? re.error(e) : E(e, l).slice(0)
                }, a = re.compile = function (e, t) {
                    var n, o = [], r = [], a = k[e + " "];
                    if (!a) {
                        for (t || (t = s(e)), n = t.length; n--;) (a = Te(t[n]))[w] ? o.push(a) : r.push(a);
                        (a = k(e, function (e, t) {
                            var n = t.length > 0, o = e.length > 0, r = function (r, s, a, l, u) {
                                var d, h, g, v = 0, y = "0", b = r && [], w = [], x = c,
                                    S = r || o && i.find.TAG("*", u), C = T += null == x ? 1 : Math.random() || .1,
                                    E = S.length;
                                for (u && (c = s === f || s || u); y !== E && null != (d = S[y]); y++) {
                                    if (o && d) {
                                        for (h = 0, s || d.ownerDocument === f || (p(d), a = !m); g = e[h++];) if (g(d, s || f, a)) {
                                            l.push(d);
                                            break
                                        }
                                        u && (T = C)
                                    }
                                    n && ((d = !g && d) && v--, r && b.push(d))
                                }
                                if (v += y, n && y !== v) {
                                    for (h = 0; g = t[h++];) g(b, w, s, a);
                                    if (r) {
                                        if (v > 0) for (; y--;) b[y] || w[y] || (w[y] = O.call(l));
                                        w = we(w)
                                    }
                                    L.apply(l, w), u && !r && w.length > 0 && v + t.length > 1 && re.uniqueSort(l)
                                }
                                return u && (T = C, c = x), b
                            };
                            return n ? ae(r) : r
                        }(r, o))).selector = e
                    }
                    return a
                }, l = re.select = function (e, t, n, o) {
                    var r, l, c, u, d, p = "function" == typeof e && e, f = !o && s(e = p.selector || e);
                    if (n = n || [], 1 === f.length) {
                        if ((l = f[0] = f[0].slice(0)).length > 2 && "ID" === (c = l[0]).type && 9 === t.nodeType && m && i.relative[l[1].type]) {
                            if (!(t = (i.find.ID(c.matches[0].replace(J, ee), t) || [])[0])) return n;
                            p && (t = t.parentNode), e = e.slice(l.shift().value.length)
                        }
                        for (r = G.needsContext.test(e) ? 0 : l.length; r-- && (c = l[r], !i.relative[u = c.type]);) if ((d = i.find[u]) && (o = d(c.matches[0].replace(J, ee), X.test(l[0].type) && me(t.parentNode) || t))) {
                            if (l.splice(r, 1), !(e = o.length && ve(l))) return L.apply(n, o), n;
                            break
                        }
                    }
                    return (p || a(e, f))(o, t, !m, n, !t || X.test(e) && me(t.parentNode) || t), n
                }, n.sortStable = w.split("").sort(_).join("") === w, n.detectDuplicates = !!d, p(), n.sortDetached = le(function (e) {
                    return 1 & e.compareDocumentPosition(f.createElement("fieldset"))
                }), le(function (e) {
                    return e.innerHTML = "<a href='#'></a>", "#" === e.firstChild.getAttribute("href")
                }) || ce("type|href|height|width", function (e, t, n) {
                    if (!n) return e.getAttribute(t, "type" === t.toLowerCase() ? 1 : 2)
                }), n.attributes && le(function (e) {
                    return e.innerHTML = "<input/>", e.firstChild.setAttribute("value", ""), "" === e.firstChild.getAttribute("value")
                }) || ce("value", function (e, t, n) {
                    if (!n && "input" === e.nodeName.toLowerCase()) return e.defaultValue
                }), le(function (e) {
                    return null == e.getAttribute("disabled")
                }) || ce(M, function (e, t, n) {
                    var i;
                    if (!n) return !0 === e[t] ? t.toLowerCase() : (i = e.getAttributeNode(t)) && i.specified ? i.value : null
                }), re
            }(n);
        S.find = k, S.expr = k.selectors, S.expr[":"] = S.expr.pseudos, S.uniqueSort = S.unique = k.uniqueSort, S.text = k.getText, S.isXMLDoc = k.isXML, S.contains = k.contains, S.escapeSelector = k.escape;
        var _ = function (e, t, n) {
            for (var i = [], o = void 0 !== n; (e = e[t]) && 9 !== e.nodeType;) if (1 === e.nodeType) {
                if (o && S(e).is(n)) break;
                i.push(e)
            }
            return i
        }, A = function (e, t) {
            for (var n = []; e; e = e.nextSibling) 1 === e.nodeType && e !== t && n.push(e);
            return n
        }, I = S.expr.match.needsContext;

        function O(e, t) {
            return e.nodeName && e.nodeName.toLowerCase() === t.toLowerCase()
        }

        var D = /^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i;

        function L(e, t, n) {
            return y(t) ? S.grep(e, function (e, i) {
                return !!t.call(e, i, e) !== n
            }) : t.nodeType ? S.grep(e, function (e) {
                return e === t !== n
            }) : "string" != typeof t ? S.grep(e, function (e) {
                return d.call(t, e) > -1 !== n
            }) : S.filter(t, e, n)
        }

        S.filter = function (e, t, n) {
            var i = t[0];
            return n && (e = ":not(" + e + ")"), 1 === t.length && 1 === i.nodeType ? S.find.matchesSelector(i, e) ? [i] : [] : S.find.matches(e, S.grep(t, function (e) {
                return 1 === e.nodeType
            }))
        }, S.fn.extend({
            find: function (e) {
                var t, n, i = this.length, o = this;
                if ("string" != typeof e) return this.pushStack(S(e).filter(function () {
                    for (t = 0; t < i; t++) if (S.contains(o[t], this)) return !0
                }));
                for (n = this.pushStack([]), t = 0; t < i; t++) S.find(e, o[t], n);
                return i > 1 ? S.uniqueSort(n) : n
            }, filter: function (e) {
                return this.pushStack(L(this, e || [], !1))
            }, not: function (e) {
                return this.pushStack(L(this, e || [], !0))
            }, is: function (e) {
                return !!L(this, "string" == typeof e && I.test(e) ? S(e) : e || [], !1).length
            }
        });
        var N, P = /^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/;
        (S.fn.init = function (e, t, n) {
            var i, o;
            if (!e) return this;
            if (n = n || N, "string" == typeof e) {
                if (!(i = "<" === e[0] && ">" === e[e.length - 1] && e.length >= 3 ? [null, e, null] : P.exec(e)) || !i[1] && t) return !t || t.jquery ? (t || n).find(e) : this.constructor(t).find(e);
                if (i[1]) {
                    if (t = t instanceof S ? t[0] : t, S.merge(this, S.parseHTML(i[1], t && t.nodeType ? t.ownerDocument || t : s, !0)), D.test(i[1]) && S.isPlainObject(t)) for (i in t) y(this[i]) ? this[i](t[i]) : this.attr(i, t[i]);
                    return this
                }
                return (o = s.getElementById(i[2])) && (this[0] = o, this.length = 1), this
            }
            return e.nodeType ? (this[0] = e, this.length = 1, this) : y(e) ? void 0 !== n.ready ? n.ready(e) : e(S) : S.makeArray(e, this)
        }).prototype = S.fn, N = S(s);
        var M = /^(?:parents|prev(?:Until|All))/, j = {children: !0, contents: !0, next: !0, prev: !0};

        function H(e, t) {
            for (; (e = e[t]) && 1 !== e.nodeType;) ;
            return e
        }

        S.fn.extend({
            has: function (e) {
                var t = S(e, this), n = t.length;
                return this.filter(function () {
                    for (var e = 0; e < n; e++) if (S.contains(this, t[e])) return !0
                })
            }, closest: function (e, t) {
                var n, i = 0, o = this.length, r = [], s = "string" != typeof e && S(e);
                if (!I.test(e)) for (; i < o; i++) for (n = this[i]; n && n !== t; n = n.parentNode) if (n.nodeType < 11 && (s ? s.index(n) > -1 : 1 === n.nodeType && S.find.matchesSelector(n, e))) {
                    r.push(n);
                    break
                }
                return this.pushStack(r.length > 1 ? S.uniqueSort(r) : r)
            }, index: function (e) {
                return e ? "string" == typeof e ? d.call(S(e), this[0]) : d.call(this, e.jquery ? e[0] : e) : this[0] && this[0].parentNode ? this.first().prevAll().length : -1
            }, add: function (e, t) {
                return this.pushStack(S.uniqueSort(S.merge(this.get(), S(e, t))))
            }, addBack: function (e) {
                return this.add(null == e ? this.prevObject : this.prevObject.filter(e))
            }
        }), S.each({
            parent: function (e) {
                var t = e.parentNode;
                return t && 11 !== t.nodeType ? t : null
            }, parents: function (e) {
                return _(e, "parentNode")
            }, parentsUntil: function (e, t, n) {
                return _(e, "parentNode", n)
            }, next: function (e) {
                return H(e, "nextSibling")
            }, prev: function (e) {
                return H(e, "previousSibling")
            }, nextAll: function (e) {
                return _(e, "nextSibling")
            }, prevAll: function (e) {
                return _(e, "previousSibling")
            }, nextUntil: function (e, t, n) {
                return _(e, "nextSibling", n)
            }, prevUntil: function (e, t, n) {
                return _(e, "previousSibling", n)
            }, siblings: function (e) {
                return A((e.parentNode || {}).firstChild, e)
            }, children: function (e) {
                return A(e.firstChild)
            }, contents: function (e) {
                return O(e, "iframe") ? e.contentDocument : (O(e, "template") && (e = e.content || e), S.merge([], e.childNodes))
            }
        }, function (e, t) {
            S.fn[e] = function (n, i) {
                var o = S.map(this, t, n);
                return "Until" !== e.slice(-5) && (i = n), i && "string" == typeof i && (o = S.filter(i, o)), this.length > 1 && (j[e] || S.uniqueSort(o), M.test(e) && o.reverse()), this.pushStack(o)
            }
        });
        var $ = /[^\x20\t\r\n\f]+/g;

        function F(e) {
            return e
        }

        function R(e) {
            throw e
        }

        function W(e, t, n, i) {
            var o;
            try {
                e && y(o = e.promise) ? o.call(e).done(t).fail(n) : e && y(o = e.then) ? o.call(e, t, n) : t.apply(void 0, [e].slice(i))
            } catch (e) {
                n.apply(void 0, [e])
            }
        }

        S.Callbacks = function (e) {
            e = "string" == typeof e ? function (e) {
                var t = {};
                return S.each(e.match($) || [], function (e, n) {
                    t[n] = !0
                }), t
            }(e) : S.extend({}, e);
            var t, n, i, o, r = [], s = [], a = -1, l = function () {
                for (o = o || e.once, i = t = !0; s.length; a = -1) for (n = s.shift(); ++a < r.length;) !1 === r[a].apply(n[0], n[1]) && e.stopOnFalse && (a = r.length, n = !1);
                e.memory || (n = !1), t = !1, o && (r = n ? [] : "")
            }, c = {
                add: function () {
                    return r && (n && !t && (a = r.length - 1, s.push(n)), function t(n) {
                        S.each(n, function (n, i) {
                            y(i) ? e.unique && c.has(i) || r.push(i) : i && i.length && "string" !== T(i) && t(i)
                        })
                    }(arguments), n && !t && l()), this
                }, remove: function () {
                    return S.each(arguments, function (e, t) {
                        for (var n; (n = S.inArray(t, r, n)) > -1;) r.splice(n, 1), n <= a && a--
                    }), this
                }, has: function (e) {
                    return e ? S.inArray(e, r) > -1 : r.length > 0
                }, empty: function () {
                    return r && (r = []), this
                }, disable: function () {
                    return o = s = [], r = n = "", this
                }, disabled: function () {
                    return !r
                }, lock: function () {
                    return o = s = [], n || t || (r = n = ""), this
                }, locked: function () {
                    return !!o
                }, fireWith: function (e, n) {
                    return o || (n = [e, (n = n || []).slice ? n.slice() : n], s.push(n), t || l()), this
                }, fire: function () {
                    return c.fireWith(this, arguments), this
                }, fired: function () {
                    return !!i
                }
            };
            return c
        }, S.extend({
            Deferred: function (e) {
                var t = [["notify", "progress", S.Callbacks("memory"), S.Callbacks("memory"), 2], ["resolve", "done", S.Callbacks("once memory"), S.Callbacks("once memory"), 0, "resolved"], ["reject", "fail", S.Callbacks("once memory"), S.Callbacks("once memory"), 1, "rejected"]],
                    i = "pending", o = {
                        state: function () {
                            return i
                        }, always: function () {
                            return r.done(arguments).fail(arguments), this
                        }, catch: function (e) {
                            return o.then(null, e)
                        }, pipe: function () {
                            var e = arguments;
                            return S.Deferred(function (n) {
                                S.each(t, function (t, i) {
                                    var o = y(e[i[4]]) && e[i[4]];
                                    r[i[1]](function () {
                                        var e = o && o.apply(this, arguments);
                                        e && y(e.promise) ? e.promise().progress(n.notify).done(n.resolve).fail(n.reject) : n[i[0] + "With"](this, o ? [e] : arguments)
                                    })
                                }), e = null
                            }).promise()
                        }, then: function (e, i, o) {
                            var r = 0;

                            function s(e, t, i, o) {
                                return function () {
                                    var a = this, l = arguments, c = function () {
                                        var n, c;
                                        if (!(e < r)) {
                                            if ((n = i.apply(a, l)) === t.promise()) throw new TypeError("Thenable self-resolution");
                                            c = n && ("object" == typeof n || "function" == typeof n) && n.then, y(c) ? o ? c.call(n, s(r, t, F, o), s(r, t, R, o)) : (r++, c.call(n, s(r, t, F, o), s(r, t, R, o), s(r, t, F, t.notifyWith))) : (i !== F && (a = void 0, l = [n]), (o || t.resolveWith)(a, l))
                                        }
                                    }, u = o ? c : function () {
                                        try {
                                            c()
                                        } catch (n) {
                                            S.Deferred.exceptionHook && S.Deferred.exceptionHook(n, u.stackTrace), e + 1 >= r && (i !== R && (a = void 0, l = [n]), t.rejectWith(a, l))
                                        }
                                    };
                                    e ? u() : (S.Deferred.getStackHook && (u.stackTrace = S.Deferred.getStackHook()), n.setTimeout(u))
                                }
                            }

                            return S.Deferred(function (n) {
                                t[0][3].add(s(0, n, y(o) ? o : F, n.notifyWith)), t[1][3].add(s(0, n, y(e) ? e : F)), t[2][3].add(s(0, n, y(i) ? i : R))
                            }).promise()
                        }, promise: function (e) {
                            return null != e ? S.extend(e, o) : o
                        }
                    }, r = {};
                return S.each(t, function (e, n) {
                    var s = n[2], a = n[5];
                    o[n[1]] = s.add, a && s.add(function () {
                        i = a
                    }, t[3 - e][2].disable, t[3 - e][3].disable, t[0][2].lock, t[0][3].lock), s.add(n[3].fire), r[n[0]] = function () {
                        return r[n[0] + "With"](this === r ? void 0 : this, arguments), this
                    }, r[n[0] + "With"] = s.fireWith
                }), o.promise(r), e && e.call(r, r), r
            }, when: function (e) {
                var t = arguments.length, n = t, i = Array(n), o = l.call(arguments), r = S.Deferred(),
                    s = function (e) {
                        return function (n) {
                            i[e] = this, o[e] = arguments.length > 1 ? l.call(arguments) : n, --t || r.resolveWith(i, o)
                        }
                    };
                if (t <= 1 && (W(e, r.done(s(n)).resolve, r.reject, !t), "pending" === r.state() || y(o[n] && o[n].then))) return r.then();
                for (; n--;) W(o[n], s(n), r.reject);
                return r.promise()
            }
        });
        var V = /^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;
        S.Deferred.exceptionHook = function (e, t) {
            n.console && n.console.warn && e && V.test(e.name) && n.console.warn("jQuery.Deferred exception: " + e.message, e.stack, t)
        }, S.readyException = function (e) {
            n.setTimeout(function () {
                throw e
            })
        };
        var z = S.Deferred();

        function q() {
            s.removeEventListener("DOMContentLoaded", q), n.removeEventListener("load", q), S.ready()
        }

        S.fn.ready = function (e) {
            return z.then(e).catch(function (e) {
                S.readyException(e)
            }), this
        }, S.extend({
            isReady: !1, readyWait: 1, ready: function (e) {
                (!0 === e ? --S.readyWait : S.isReady) || (S.isReady = !0, !0 !== e && --S.readyWait > 0 || z.resolveWith(s, [S]))
            }
        }), S.ready.then = z.then, "complete" === s.readyState || "loading" !== s.readyState && !s.documentElement.doScroll ? n.setTimeout(S.ready) : (s.addEventListener("DOMContentLoaded", q), n.addEventListener("load", q));
        var B = function (e, t, n, i, o, r, s) {
            var a = 0, l = e.length, c = null == n;
            if ("object" === T(n)) for (a in o = !0, n) B(e, t, a, n[a], !0, r, s); else if (void 0 !== i && (o = !0, y(i) || (s = !0), c && (s ? (t.call(e, i), t = null) : (c = t, t = function (e, t, n) {
                return c.call(S(e), n)
            })), t)) for (; a < l; a++) t(e[a], n, s ? i : i.call(e[a], a, t(e[a], n)));
            return o ? e : c ? t.call(e) : l ? t(e[0], n) : r
        }, U = /^-ms-/, G = /-([a-z])/g;

        function Y(e, t) {
            return t.toUpperCase()
        }

        function K(e) {
            return e.replace(U, "ms-").replace(G, Y)
        }

        var Q = function (e) {
            return 1 === e.nodeType || 9 === e.nodeType || !+e.nodeType
        };

        function Z() {
            this.expando = S.expando + Z.uid++
        }

        Z.uid = 1, Z.prototype = {
            cache: function (e) {
                var t = e[this.expando];
                return t || (t = {}, Q(e) && (e.nodeType ? e[this.expando] = t : Object.defineProperty(e, this.expando, {
                    value: t,
                    configurable: !0
                }))), t
            }, set: function (e, t, n) {
                var i, o = this.cache(e);
                if ("string" == typeof t) o[K(t)] = n; else for (i in t) o[K(i)] = t[i];
                return o
            }, get: function (e, t) {
                return void 0 === t ? this.cache(e) : e[this.expando] && e[this.expando][K(t)]
            }, access: function (e, t, n) {
                return void 0 === t || t && "string" == typeof t && void 0 === n ? this.get(e, t) : (this.set(e, t, n), void 0 !== n ? n : t)
            }, remove: function (e, t) {
                var n, i = e[this.expando];
                if (void 0 !== i) {
                    if (void 0 !== t) {
                        n = (t = Array.isArray(t) ? t.map(K) : (t = K(t)) in i ? [t] : t.match($) || []).length;
                        for (; n--;) delete i[t[n]]
                    }
                    (void 0 === t || S.isEmptyObject(i)) && (e.nodeType ? e[this.expando] = void 0 : delete e[this.expando])
                }
            }, hasData: function (e) {
                var t = e[this.expando];
                return void 0 !== t && !S.isEmptyObject(t)
            }
        };
        var X = new Z, J = new Z, ee = /^(?:\{[\w\W]*\}|\[[\w\W]*\])$/, te = /[A-Z]/g;

        function ne(e, t, n) {
            var i;
            if (void 0 === n && 1 === e.nodeType) if (i = "data-" + t.replace(te, "-$&").toLowerCase(), "string" == typeof (n = e.getAttribute(i))) {
                try {
                    n = function (e) {
                        return "true" === e || "false" !== e && ("null" === e ? null : e === +e + "" ? +e : ee.test(e) ? JSON.parse(e) : e)
                    }(n)
                } catch (e) {
                }
                J.set(e, t, n)
            } else n = void 0;
            return n
        }

        S.extend({
            hasData: function (e) {
                return J.hasData(e) || X.hasData(e)
            }, data: function (e, t, n) {
                return J.access(e, t, n)
            }, removeData: function (e, t) {
                J.remove(e, t)
            }, _data: function (e, t, n) {
                return X.access(e, t, n)
            }, _removeData: function (e, t) {
                X.remove(e, t)
            }
        }), S.fn.extend({
            data: function (e, t) {
                var n, i, o, r = this[0], s = r && r.attributes;
                if (void 0 === e) {
                    if (this.length && (o = J.get(r), 1 === r.nodeType && !X.get(r, "hasDataAttrs"))) {
                        for (n = s.length; n--;) s[n] && 0 === (i = s[n].name).indexOf("data-") && (i = K(i.slice(5)), ne(r, i, o[i]));
                        X.set(r, "hasDataAttrs", !0)
                    }
                    return o
                }
                return "object" == typeof e ? this.each(function () {
                    J.set(this, e)
                }) : B(this, function (t) {
                    var n;
                    if (r && void 0 === t) return void 0 !== (n = J.get(r, e)) ? n : void 0 !== (n = ne(r, e)) ? n : void 0;
                    this.each(function () {
                        J.set(this, e, t)
                    })
                }, null, t, arguments.length > 1, null, !0)
            }, removeData: function (e) {
                return this.each(function () {
                    J.remove(this, e)
                })
            }
        }), S.extend({
            queue: function (e, t, n) {
                var i;
                if (e) return t = (t || "fx") + "queue", i = X.get(e, t), n && (!i || Array.isArray(n) ? i = X.access(e, t, S.makeArray(n)) : i.push(n)), i || []
            }, dequeue: function (e, t) {
                t = t || "fx";
                var n = S.queue(e, t), i = n.length, o = n.shift(), r = S._queueHooks(e, t);
                "inprogress" === o && (o = n.shift(), i--), o && ("fx" === t && n.unshift("inprogress"), delete r.stop, o.call(e, function () {
                    S.dequeue(e, t)
                }, r)), !i && r && r.empty.fire()
            }, _queueHooks: function (e, t) {
                var n = t + "queueHooks";
                return X.get(e, n) || X.access(e, n, {
                    empty: S.Callbacks("once memory").add(function () {
                        X.remove(e, [t + "queue", n])
                    })
                })
            }
        }), S.fn.extend({
            queue: function (e, t) {
                var n = 2;
                return "string" != typeof e && (t = e, e = "fx", n--), arguments.length < n ? S.queue(this[0], e) : void 0 === t ? this : this.each(function () {
                    var n = S.queue(this, e, t);
                    S._queueHooks(this, e), "fx" === e && "inprogress" !== n[0] && S.dequeue(this, e)
                })
            }, dequeue: function (e) {
                return this.each(function () {
                    S.dequeue(this, e)
                })
            }, clearQueue: function (e) {
                return this.queue(e || "fx", [])
            }, promise: function (e, t) {
                var n, i = 1, o = S.Deferred(), r = this, s = this.length, a = function () {
                    --i || o.resolveWith(r, [r])
                };
                for ("string" != typeof e && (t = e, e = void 0), e = e || "fx"; s--;) (n = X.get(r[s], e + "queueHooks")) && n.empty && (i++, n.empty.add(a));
                return a(), o.promise(t)
            }
        });
        var ie = /[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,
            oe = new RegExp("^(?:([+-])=|)(" + ie + ")([a-z%]*)$", "i"), re = ["Top", "Right", "Bottom", "Left"],
            se = function (e, t) {
                return "none" === (e = t || e).style.display || "" === e.style.display && S.contains(e.ownerDocument, e) && "none" === S.css(e, "display")
            }, ae = function (e, t, n, i) {
                var o, r, s = {};
                for (r in t) s[r] = e.style[r], e.style[r] = t[r];
                for (r in o = n.apply(e, i || []), t) e.style[r] = s[r];
                return o
            };

        function le(e, t, n, i) {
            var o, r, s = 20, a = i ? function () {
                    return i.cur()
                } : function () {
                    return S.css(e, t, "")
                }, l = a(), c = n && n[3] || (S.cssNumber[t] ? "" : "px"),
                u = (S.cssNumber[t] || "px" !== c && +l) && oe.exec(S.css(e, t));
            if (u && u[3] !== c) {
                for (l /= 2, c = c || u[3], u = +l || 1; s--;) S.style(e, t, u + c), (1 - r) * (1 - (r = a() / l || .5)) <= 0 && (s = 0), u /= r;
                u *= 2, S.style(e, t, u + c), n = n || []
            }
            return n && (u = +u || +l || 0, o = n[1] ? u + (n[1] + 1) * n[2] : +n[2], i && (i.unit = c, i.start = u, i.end = o)), o
        }

        var ce = {};

        function ue(e) {
            var t, n = e.ownerDocument, i = e.nodeName, o = ce[i];
            return o || (t = n.body.appendChild(n.createElement(i)), o = S.css(t, "display"), t.parentNode.removeChild(t), "none" === o && (o = "block"), ce[i] = o, o)
        }

        function de(e, t) {
            for (var n, i, o = [], r = 0, s = e.length; r < s; r++) (i = e[r]).style && (n = i.style.display, t ? ("none" === n && (o[r] = X.get(i, "display") || null, o[r] || (i.style.display = "")), "" === i.style.display && se(i) && (o[r] = ue(i))) : "none" !== n && (o[r] = "none", X.set(i, "display", n)));
            for (r = 0; r < s; r++) null != o[r] && (e[r].style.display = o[r]);
            return e
        }

        S.fn.extend({
            show: function () {
                return de(this, !0)
            }, hide: function () {
                return de(this)
            }, toggle: function (e) {
                return "boolean" == typeof e ? e ? this.show() : this.hide() : this.each(function () {
                    se(this) ? S(this).show() : S(this).hide()
                })
            }
        });
        var pe = /^(?:checkbox|radio)$/i, fe = /<([a-z][^\/\0>\x20\t\r\n\f]+)/i,
            he = /^$|^module$|\/(?:java|ecma)script/i, me = {
                option: [1, "<select multiple='multiple'>", "</select>"],
                thead: [1, "<table>", "</table>"],
                col: [2, "<table><colgroup>", "</colgroup></table>"],
                tr: [2, "<table><tbody>", "</tbody></table>"],
                td: [3, "<table><tbody><tr>", "</tr></tbody></table>"],
                _default: [0, "", ""]
            };

        function ge(e, t) {
            var n;
            return n = void 0 !== e.getElementsByTagName ? e.getElementsByTagName(t || "*") : void 0 !== e.querySelectorAll ? e.querySelectorAll(t || "*") : [], void 0 === t || t && O(e, t) ? S.merge([e], n) : n
        }

        function ve(e, t) {
            for (var n = 0, i = e.length; n < i; n++) X.set(e[n], "globalEval", !t || X.get(t[n], "globalEval"))
        }

        me.optgroup = me.option, me.tbody = me.tfoot = me.colgroup = me.caption = me.thead, me.th = me.td;
        var ye = /<|&#?\w+;/;

        function be(e, t, n, i, o) {
            for (var r, s, a, l, c, u, d = t.createDocumentFragment(), p = [], f = 0, h = e.length; f < h; f++) if ((r = e[f]) || 0 === r) if ("object" === T(r)) S.merge(p, r.nodeType ? [r] : r); else if (ye.test(r)) {
                for (s = s || d.appendChild(t.createElement("div")), a = (fe.exec(r) || ["", ""])[1].toLowerCase(), l = me[a] || me._default, s.innerHTML = l[1] + S.htmlPrefilter(r) + l[2], u = l[0]; u--;) s = s.lastChild;
                S.merge(p, s.childNodes), (s = d.firstChild).textContent = ""
            } else p.push(t.createTextNode(r));
            for (d.textContent = "", f = 0; r = p[f++];) if (i && S.inArray(r, i) > -1) o && o.push(r); else if (c = S.contains(r.ownerDocument, r), s = ge(d.appendChild(r), "script"), c && ve(s), n) for (u = 0; r = s[u++];) he.test(r.type || "") && n.push(r);
            return d
        }

        !function () {
            var e = s.createDocumentFragment().appendChild(s.createElement("div")), t = s.createElement("input");
            t.setAttribute("type", "radio"), t.setAttribute("checked", "checked"), t.setAttribute("name", "t"), e.appendChild(t), v.checkClone = e.cloneNode(!0).cloneNode(!0).lastChild.checked, e.innerHTML = "<textarea>x</textarea>", v.noCloneChecked = !!e.cloneNode(!0).lastChild.defaultValue
        }();
        var we = s.documentElement, xe = /^key/, Te = /^(?:mouse|pointer|contextmenu|drag|drop)|click/,
            Se = /^([^.]*)(?:\.(.+)|)/;

        function Ce() {
            return !0
        }

        function Ee() {
            return !1
        }

        function ke() {
            try {
                return s.activeElement
            } catch (e) {
            }
        }

        function _e(e, t, n, i, o, r) {
            var s, a;
            if ("object" == typeof t) {
                for (a in "string" != typeof n && (i = i || n, n = void 0), t) _e(e, a, n, i, t[a], r);
                return e
            }
            if (null == i && null == o ? (o = n, i = n = void 0) : null == o && ("string" == typeof n ? (o = i, i = void 0) : (o = i, i = n, n = void 0)), !1 === o) o = Ee; else if (!o) return e;
            return 1 === r && (s = o, (o = function (e) {
                return S().off(e), s.apply(this, arguments)
            }).guid = s.guid || (s.guid = S.guid++)), e.each(function () {
                S.event.add(this, t, o, i, n)
            })
        }

        S.event = {
            global: {}, add: function (e, t, n, i, o) {
                var r, s, a, l, c, u, d, p, f, h, m, g = X.get(e);
                if (g) for (n.handler && (n = (r = n).handler, o = r.selector), o && S.find.matchesSelector(we, o), n.guid || (n.guid = S.guid++), (l = g.events) || (l = g.events = {}), (s = g.handle) || (s = g.handle = function (t) {
                    return void 0 !== S && S.event.triggered !== t.type ? S.event.dispatch.apply(e, arguments) : void 0
                }), c = (t = (t || "").match($) || [""]).length; c--;) f = m = (a = Se.exec(t[c]) || [])[1], h = (a[2] || "").split(".").sort(), f && (d = S.event.special[f] || {}, f = (o ? d.delegateType : d.bindType) || f, d = S.event.special[f] || {}, u = S.extend({
                    type: f,
                    origType: m,
                    data: i,
                    handler: n,
                    guid: n.guid,
                    selector: o,
                    needsContext: o && S.expr.match.needsContext.test(o),
                    namespace: h.join(".")
                }, r), (p = l[f]) || ((p = l[f] = []).delegateCount = 0, d.setup && !1 !== d.setup.call(e, i, h, s) || e.addEventListener && e.addEventListener(f, s)), d.add && (d.add.call(e, u), u.handler.guid || (u.handler.guid = n.guid)), o ? p.splice(p.delegateCount++, 0, u) : p.push(u), S.event.global[f] = !0)
            }, remove: function (e, t, n, i, o) {
                var r, s, a, l, c, u, d, p, f, h, m, g = X.hasData(e) && X.get(e);
                if (g && (l = g.events)) {
                    for (c = (t = (t || "").match($) || [""]).length; c--;) if (f = m = (a = Se.exec(t[c]) || [])[1], h = (a[2] || "").split(".").sort(), f) {
                        for (d = S.event.special[f] || {}, p = l[f = (i ? d.delegateType : d.bindType) || f] || [], a = a[2] && new RegExp("(^|\\.)" + h.join("\\.(?:.*\\.|)") + "(\\.|$)"), s = r = p.length; r--;) u = p[r], !o && m !== u.origType || n && n.guid !== u.guid || a && !a.test(u.namespace) || i && i !== u.selector && ("**" !== i || !u.selector) || (p.splice(r, 1), u.selector && p.delegateCount--, d.remove && d.remove.call(e, u));
                        s && !p.length && (d.teardown && !1 !== d.teardown.call(e, h, g.handle) || S.removeEvent(e, f, g.handle), delete l[f])
                    } else for (f in l) S.event.remove(e, f + t[c], n, i, !0);
                    S.isEmptyObject(l) && X.remove(e, "handle events")
                }
            }, dispatch: function (e) {
                var t, n, i, o, r, s, a = S.event.fix(e), l = new Array(arguments.length),
                    c = (X.get(this, "events") || {})[a.type] || [], u = S.event.special[a.type] || {};
                for (l[0] = a, t = 1; t < arguments.length; t++) l[t] = arguments[t];
                if (a.delegateTarget = this, !u.preDispatch || !1 !== u.preDispatch.call(this, a)) {
                    for (s = S.event.handlers.call(this, a, c), t = 0; (o = s[t++]) && !a.isPropagationStopped();) for (a.currentTarget = o.elem, n = 0; (r = o.handlers[n++]) && !a.isImmediatePropagationStopped();) a.rnamespace && !a.rnamespace.test(r.namespace) || (a.handleObj = r, a.data = r.data, void 0 !== (i = ((S.event.special[r.origType] || {}).handle || r.handler).apply(o.elem, l)) && !1 === (a.result = i) && (a.preventDefault(), a.stopPropagation()));
                    return u.postDispatch && u.postDispatch.call(this, a), a.result
                }
            }, handlers: function (e, t) {
                var n, i, o, r, s, a = [], l = t.delegateCount, c = e.target;
                if (l && c.nodeType && !("click" === e.type && e.button >= 1)) for (; c !== this; c = c.parentNode || this) if (1 === c.nodeType && ("click" !== e.type || !0 !== c.disabled)) {
                    for (r = [], s = {}, n = 0; n < l; n++) void 0 === s[o = (i = t[n]).selector + " "] && (s[o] = i.needsContext ? S(o, this).index(c) > -1 : S.find(o, this, null, [c]).length), s[o] && r.push(i);
                    r.length && a.push({elem: c, handlers: r})
                }
                return c = this, l < t.length && a.push({elem: c, handlers: t.slice(l)}), a
            }, addProp: function (e, t) {
                Object.defineProperty(S.Event.prototype, e, {
                    enumerable: !0, configurable: !0, get: y(t) ? function () {
                        if (this.originalEvent) return t(this.originalEvent)
                    } : function () {
                        if (this.originalEvent) return this.originalEvent[e]
                    }, set: function (t) {
                        Object.defineProperty(this, e, {enumerable: !0, configurable: !0, writable: !0, value: t})
                    }
                })
            }, fix: function (e) {
                return e[S.expando] ? e : new S.Event(e)
            }, special: {
                load: {noBubble: !0}, focus: {
                    trigger: function () {
                        if (this !== ke() && this.focus) return this.focus(), !1
                    }, delegateType: "focusin"
                }, blur: {
                    trigger: function () {
                        if (this === ke() && this.blur) return this.blur(), !1
                    }, delegateType: "focusout"
                }, click: {
                    trigger: function () {
                        if ("checkbox" === this.type && this.click && O(this, "input")) return this.click(), !1
                    }, _default: function (e) {
                        return O(e.target, "a")
                    }
                }, beforeunload: {
                    postDispatch: function (e) {
                        void 0 !== e.result && e.originalEvent && (e.originalEvent.returnValue = e.result)
                    }
                }
            }
        }, S.removeEvent = function (e, t, n) {
            e.removeEventListener && e.removeEventListener(t, n)
        }, S.Event = function (e, t) {
            if (!(this instanceof S.Event)) return new S.Event(e, t);
            e && e.type ? (this.originalEvent = e, this.type = e.type, this.isDefaultPrevented = e.defaultPrevented || void 0 === e.defaultPrevented && !1 === e.returnValue ? Ce : Ee, this.target = e.target && 3 === e.target.nodeType ? e.target.parentNode : e.target, this.currentTarget = e.currentTarget, this.relatedTarget = e.relatedTarget) : this.type = e, t && S.extend(this, t), this.timeStamp = e && e.timeStamp || Date.now(), this[S.expando] = !0
        }, S.Event.prototype = {
            constructor: S.Event,
            isDefaultPrevented: Ee,
            isPropagationStopped: Ee,
            isImmediatePropagationStopped: Ee,
            isSimulated: !1,
            preventDefault: function () {
                var e = this.originalEvent;
                this.isDefaultPrevented = Ce, e && !this.isSimulated && e.preventDefault()
            },
            stopPropagation: function () {
                var e = this.originalEvent;
                this.isPropagationStopped = Ce, e && !this.isSimulated && e.stopPropagation()
            },
            stopImmediatePropagation: function () {
                var e = this.originalEvent;
                this.isImmediatePropagationStopped = Ce, e && !this.isSimulated && e.stopImmediatePropagation(), this.stopPropagation()
            }
        }, S.each({
            altKey: !0,
            bubbles: !0,
            cancelable: !0,
            changedTouches: !0,
            ctrlKey: !0,
            detail: !0,
            eventPhase: !0,
            metaKey: !0,
            pageX: !0,
            pageY: !0,
            shiftKey: !0,
            view: !0,
            char: !0,
            charCode: !0,
            key: !0,
            keyCode: !0,
            button: !0,
            buttons: !0,
            clientX: !0,
            clientY: !0,
            offsetX: !0,
            offsetY: !0,
            pointerId: !0,
            pointerType: !0,
            screenX: !0,
            screenY: !0,
            targetTouches: !0,
            toElement: !0,
            touches: !0,
            which: function (e) {
                var t = e.button;
                return null == e.which && xe.test(e.type) ? null != e.charCode ? e.charCode : e.keyCode : !e.which && void 0 !== t && Te.test(e.type) ? 1 & t ? 1 : 2 & t ? 3 : 4 & t ? 2 : 0 : e.which
            }
        }, S.event.addProp), S.each({
            mouseenter: "mouseover",
            mouseleave: "mouseout",
            pointerenter: "pointerover",
            pointerleave: "pointerout"
        }, function (e, t) {
            S.event.special[e] = {
                delegateType: t, bindType: t, handle: function (e) {
                    var n, i = e.relatedTarget, o = e.handleObj;
                    return i && (i === this || S.contains(this, i)) || (e.type = o.origType, n = o.handler.apply(this, arguments), e.type = t), n
                }
            }
        }), S.fn.extend({
            on: function (e, t, n, i) {
                return _e(this, e, t, n, i)
            }, one: function (e, t, n, i) {
                return _e(this, e, t, n, i, 1)
            }, off: function (e, t, n) {
                var i, o;
                if (e && e.preventDefault && e.handleObj) return i = e.handleObj, S(e.delegateTarget).off(i.namespace ? i.origType + "." + i.namespace : i.origType, i.selector, i.handler), this;
                if ("object" == typeof e) {
                    for (o in e) this.off(o, t, e[o]);
                    return this
                }
                return !1 !== t && "function" != typeof t || (n = t, t = void 0), !1 === n && (n = Ee), this.each(function () {
                    S.event.remove(this, e, n, t)
                })
            }
        });
        var Ae = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([a-z][^\/\0>\x20\t\r\n\f]*)[^>]*)\/>/gi,
            Ie = /<script|<style|<link/i, Oe = /checked\s*(?:[^=]|=\s*.checked.)/i,
            De = /^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g;

        function Le(e, t) {
            return O(e, "table") && O(11 !== t.nodeType ? t : t.firstChild, "tr") && S(e).children("tbody")[0] || e
        }

        function Ne(e) {
            return e.type = (null !== e.getAttribute("type")) + "/" + e.type, e
        }

        function Pe(e) {
            return "true/" === (e.type || "").slice(0, 5) ? e.type = e.type.slice(5) : e.removeAttribute("type"), e
        }

        function Me(e, t) {
            var n, i, o, r, s, a, l, c;
            if (1 === t.nodeType) {
                if (X.hasData(e) && (r = X.access(e), s = X.set(t, r), c = r.events)) for (o in delete s.handle, s.events = {}, c) for (n = 0, i = c[o].length; n < i; n++) S.event.add(t, o, c[o][n]);
                J.hasData(e) && (a = J.access(e), l = S.extend({}, a), J.set(t, l))
            }
        }

        function je(e, t) {
            var n = t.nodeName.toLowerCase();
            "input" === n && pe.test(e.type) ? t.checked = e.checked : "input" !== n && "textarea" !== n || (t.defaultValue = e.defaultValue)
        }

        function He(e, t, n, i) {
            t = c.apply([], t);
            var o, r, s, a, l, u, d = 0, p = e.length, f = p - 1, h = t[0], m = y(h);
            if (m || p > 1 && "string" == typeof h && !v.checkClone && Oe.test(h)) return e.each(function (o) {
                var r = e.eq(o);
                m && (t[0] = h.call(this, o, r.html())), He(r, t, n, i)
            });
            if (p && (r = (o = be(t, e[0].ownerDocument, !1, e, i)).firstChild, 1 === o.childNodes.length && (o = r), r || i)) {
                for (a = (s = S.map(ge(o, "script"), Ne)).length; d < p; d++) l = o, d !== f && (l = S.clone(l, !0, !0), a && S.merge(s, ge(l, "script"))), n.call(e[d], l, d);
                if (a) for (u = s[s.length - 1].ownerDocument, S.map(s, Pe), d = 0; d < a; d++) l = s[d], he.test(l.type || "") && !X.access(l, "globalEval") && S.contains(u, l) && (l.src && "module" !== (l.type || "").toLowerCase() ? S._evalUrl && S._evalUrl(l.src) : x(l.textContent.replace(De, ""), u, l))
            }
            return e
        }

        function $e(e, t, n) {
            for (var i, o = t ? S.filter(t, e) : e, r = 0; null != (i = o[r]); r++) n || 1 !== i.nodeType || S.cleanData(ge(i)), i.parentNode && (n && S.contains(i.ownerDocument, i) && ve(ge(i, "script")), i.parentNode.removeChild(i));
            return e
        }

        S.extend({
            htmlPrefilter: function (e) {
                return e.replace(Ae, "<$1></$2>")
            }, clone: function (e, t, n) {
                var i, o, r, s, a = e.cloneNode(!0), l = S.contains(e.ownerDocument, e);
                if (!(v.noCloneChecked || 1 !== e.nodeType && 11 !== e.nodeType || S.isXMLDoc(e))) for (s = ge(a), i = 0, o = (r = ge(e)).length; i < o; i++) je(r[i], s[i]);
                if (t) if (n) for (r = r || ge(e), s = s || ge(a), i = 0, o = r.length; i < o; i++) Me(r[i], s[i]); else Me(e, a);
                return (s = ge(a, "script")).length > 0 && ve(s, !l && ge(e, "script")), a
            }, cleanData: function (e) {
                for (var t, n, i, o = S.event.special, r = 0; void 0 !== (n = e[r]); r++) if (Q(n)) {
                    if (t = n[X.expando]) {
                        if (t.events) for (i in t.events) o[i] ? S.event.remove(n, i) : S.removeEvent(n, i, t.handle);
                        n[X.expando] = void 0
                    }
                    n[J.expando] && (n[J.expando] = void 0)
                }
            }
        }), S.fn.extend({
            detach: function (e) {
                return $e(this, e, !0)
            }, remove: function (e) {
                return $e(this, e)
            }, text: function (e) {
                return B(this, function (e) {
                    return void 0 === e ? S.text(this) : this.empty().each(function () {
                        1 !== this.nodeType && 11 !== this.nodeType && 9 !== this.nodeType || (this.textContent = e)
                    })
                }, null, e, arguments.length)
            }, append: function () {
                return He(this, arguments, function (e) {
                    1 !== this.nodeType && 11 !== this.nodeType && 9 !== this.nodeType || Le(this, e).appendChild(e)
                })
            }, prepend: function () {
                return He(this, arguments, function (e) {
                    if (1 === this.nodeType || 11 === this.nodeType || 9 === this.nodeType) {
                        var t = Le(this, e);
                        t.insertBefore(e, t.firstChild)
                    }
                })
            }, before: function () {
                return He(this, arguments, function (e) {
                    this.parentNode && this.parentNode.insertBefore(e, this)
                })
            }, after: function () {
                return He(this, arguments, function (e) {
                    this.parentNode && this.parentNode.insertBefore(e, this.nextSibling)
                })
            }, empty: function () {
                for (var e, t = 0; null != (e = this[t]); t++) 1 === e.nodeType && (S.cleanData(ge(e, !1)), e.textContent = "");
                return this
            }, clone: function (e, t) {
                return e = null != e && e, t = null == t ? e : t, this.map(function () {
                    return S.clone(this, e, t)
                })
            }, html: function (e) {
                return B(this, function (e) {
                    var t = this[0] || {}, n = 0, i = this.length;
                    if (void 0 === e && 1 === t.nodeType) return t.innerHTML;
                    if ("string" == typeof e && !Ie.test(e) && !me[(fe.exec(e) || ["", ""])[1].toLowerCase()]) {
                        e = S.htmlPrefilter(e);
                        try {
                            for (; n < i; n++) 1 === (t = this[n] || {}).nodeType && (S.cleanData(ge(t, !1)), t.innerHTML = e);
                            t = 0
                        } catch (e) {
                        }
                    }
                    t && this.empty().append(e)
                }, null, e, arguments.length)
            }, replaceWith: function () {
                var e = [];
                return He(this, arguments, function (t) {
                    var n = this.parentNode;
                    S.inArray(this, e) < 0 && (S.cleanData(ge(this)), n && n.replaceChild(t, this))
                }, e)
            }
        }), S.each({
            appendTo: "append",
            prependTo: "prepend",
            insertBefore: "before",
            insertAfter: "after",
            replaceAll: "replaceWith"
        }, function (e, t) {
            S.fn[e] = function (e) {
                for (var n, i = [], o = S(e), r = o.length - 1, s = 0; s <= r; s++) n = s === r ? this : this.clone(!0), S(o[s])[t](n), u.apply(i, n.get());
                return this.pushStack(i)
            }
        });
        var Fe = new RegExp("^(" + ie + ")(?!px)[a-z%]+$", "i"), Re = function (e) {
            var t = e.ownerDocument.defaultView;
            return t && t.opener || (t = n), t.getComputedStyle(e)
        }, We = new RegExp(re.join("|"), "i");

        function Ve(e, t, n) {
            var i, o, r, s, a = e.style;
            return (n = n || Re(e)) && ("" !== (s = n.getPropertyValue(t) || n[t]) || S.contains(e.ownerDocument, e) || (s = S.style(e, t)), !v.pixelBoxStyles() && Fe.test(s) && We.test(t) && (i = a.width, o = a.minWidth, r = a.maxWidth, a.minWidth = a.maxWidth = a.width = s, s = n.width, a.width = i, a.minWidth = o, a.maxWidth = r)), void 0 !== s ? s + "" : s
        }

        function ze(e, t) {
            return {
                get: function () {
                    if (!e()) return (this.get = t).apply(this, arguments);
                    delete this.get
                }
            }
        }

        !function () {
            function e() {
                if (u) {
                    c.style.cssText = "position:absolute;left:-11111px;width:60px;margin-top:1px;padding:0;border:0", u.style.cssText = "position:relative;display:block;box-sizing:border-box;overflow:scroll;margin:auto;border:1px;padding:1px;width:60%;top:1%", we.appendChild(c).appendChild(u);
                    var e = n.getComputedStyle(u);
                    i = "1%" !== e.top, l = 12 === t(e.marginLeft), u.style.right = "60%", a = 36 === t(e.right), o = 36 === t(e.width), u.style.position = "absolute", r = 36 === u.offsetWidth || "absolute", we.removeChild(c), u = null
                }
            }

            function t(e) {
                return Math.round(parseFloat(e))
            }

            var i, o, r, a, l, c = s.createElement("div"), u = s.createElement("div");
            u.style && (u.style.backgroundClip = "content-box", u.cloneNode(!0).style.backgroundClip = "", v.clearCloneStyle = "content-box" === u.style.backgroundClip, S.extend(v, {
                boxSizingReliable: function () {
                    return e(), o
                }, pixelBoxStyles: function () {
                    return e(), a
                }, pixelPosition: function () {
                    return e(), i
                }, reliableMarginLeft: function () {
                    return e(), l
                }, scrollboxSize: function () {
                    return e(), r
                }
            }))
        }();
        var qe = /^(none|table(?!-c[ea]).+)/, Be = /^--/,
            Ue = {position: "absolute", visibility: "hidden", display: "block"},
            Ge = {letterSpacing: "0", fontWeight: "400"}, Ye = ["Webkit", "Moz", "ms"],
            Ke = s.createElement("div").style;

        function Qe(e) {
            var t = S.cssProps[e];
            return t || (t = S.cssProps[e] = function (e) {
                if (e in Ke) return e;
                for (var t = e[0].toUpperCase() + e.slice(1), n = Ye.length; n--;) if ((e = Ye[n] + t) in Ke) return e
            }(e) || e), t
        }

        function Ze(e, t, n) {
            var i = oe.exec(t);
            return i ? Math.max(0, i[2] - (n || 0)) + (i[3] || "px") : t
        }

        function Xe(e, t, n, i, o, r) {
            var s = "width" === t ? 1 : 0, a = 0, l = 0;
            if (n === (i ? "border" : "content")) return 0;
            for (; s < 4; s += 2) "margin" === n && (l += S.css(e, n + re[s], !0, o)), i ? ("content" === n && (l -= S.css(e, "padding" + re[s], !0, o)), "margin" !== n && (l -= S.css(e, "border" + re[s] + "Width", !0, o))) : (l += S.css(e, "padding" + re[s], !0, o), "padding" !== n ? l += S.css(e, "border" + re[s] + "Width", !0, o) : a += S.css(e, "border" + re[s] + "Width", !0, o));
            return !i && r >= 0 && (l += Math.max(0, Math.ceil(e["offset" + t[0].toUpperCase() + t.slice(1)] - r - l - a - .5))), l
        }

        function Je(e, t, n) {
            var i = Re(e), o = Ve(e, t, i), r = "border-box" === S.css(e, "boxSizing", !1, i), s = r;
            if (Fe.test(o)) {
                if (!n) return o;
                o = "auto"
            }
            return s = s && (v.boxSizingReliable() || o === e.style[t]), ("auto" === o || !parseFloat(o) && "inline" === S.css(e, "display", !1, i)) && (o = e["offset" + t[0].toUpperCase() + t.slice(1)], s = !0), (o = parseFloat(o) || 0) + Xe(e, t, n || (r ? "border" : "content"), s, i, o) + "px"
        }

        function et(e, t, n, i, o) {
            return new et.prototype.init(e, t, n, i, o)
        }

        S.extend({
            cssHooks: {
                opacity: {
                    get: function (e, t) {
                        if (t) {
                            var n = Ve(e, "opacity");
                            return "" === n ? "1" : n
                        }
                    }
                }
            },
            cssNumber: {
                animationIterationCount: !0,
                columnCount: !0,
                fillOpacity: !0,
                flexGrow: !0,
                flexShrink: !0,
                fontWeight: !0,
                lineHeight: !0,
                opacity: !0,
                order: !0,
                orphans: !0,
                widows: !0,
                zIndex: !0,
                zoom: !0
            },
            cssProps: {},
            style: function (e, t, n, i) {
                if (e && 3 !== e.nodeType && 8 !== e.nodeType && e.style) {
                    var o, r, s, a = K(t), l = Be.test(t), c = e.style;
                    if (l || (t = Qe(a)), s = S.cssHooks[t] || S.cssHooks[a], void 0 === n) return s && "get" in s && void 0 !== (o = s.get(e, !1, i)) ? o : c[t];
                    "string" === (r = typeof n) && (o = oe.exec(n)) && o[1] && (n = le(e, t, o), r = "number"), null != n && n == n && ("number" === r && (n += o && o[3] || (S.cssNumber[a] ? "" : "px")), v.clearCloneStyle || "" !== n || 0 !== t.indexOf("background") || (c[t] = "inherit"), s && "set" in s && void 0 === (n = s.set(e, n, i)) || (l ? c.setProperty(t, n) : c[t] = n))
                }
            },
            css: function (e, t, n, i) {
                var o, r, s, a = K(t);
                return Be.test(t) || (t = Qe(a)), (s = S.cssHooks[t] || S.cssHooks[a]) && "get" in s && (o = s.get(e, !0, n)), void 0 === o && (o = Ve(e, t, i)), "normal" === o && t in Ge && (o = Ge[t]), "" === n || n ? (r = parseFloat(o), !0 === n || isFinite(r) ? r || 0 : o) : o
            }
        }), S.each(["height", "width"], function (e, t) {
            S.cssHooks[t] = {
                get: function (e, n, i) {
                    if (n) return !qe.test(S.css(e, "display")) || e.getClientRects().length && e.getBoundingClientRect().width ? Je(e, t, i) : ae(e, Ue, function () {
                        return Je(e, t, i)
                    })
                }, set: function (e, n, i) {
                    var o, r = Re(e), s = "border-box" === S.css(e, "boxSizing", !1, r), a = i && Xe(e, t, i, s, r);
                    return s && v.scrollboxSize() === r.position && (a -= Math.ceil(e["offset" + t[0].toUpperCase() + t.slice(1)] - parseFloat(r[t]) - Xe(e, t, "border", !1, r) - .5)), a && (o = oe.exec(n)) && "px" !== (o[3] || "px") && (e.style[t] = n, n = S.css(e, t)), Ze(0, n, a)
                }
            }
        }), S.cssHooks.marginLeft = ze(v.reliableMarginLeft, function (e, t) {
            if (t) return (parseFloat(Ve(e, "marginLeft")) || e.getBoundingClientRect().left - ae(e, {marginLeft: 0}, function () {
                return e.getBoundingClientRect().left
            })) + "px"
        }), S.each({margin: "", padding: "", border: "Width"}, function (e, t) {
            S.cssHooks[e + t] = {
                expand: function (n) {
                    for (var i = 0, o = {}, r = "string" == typeof n ? n.split(" ") : [n]; i < 4; i++) o[e + re[i] + t] = r[i] || r[i - 2] || r[0];
                    return o
                }
            }, "margin" !== e && (S.cssHooks[e + t].set = Ze)
        }), S.fn.extend({
            css: function (e, t) {
                return B(this, function (e, t, n) {
                    var i, o, r = {}, s = 0;
                    if (Array.isArray(t)) {
                        for (i = Re(e), o = t.length; s < o; s++) r[t[s]] = S.css(e, t[s], !1, i);
                        return r
                    }
                    return void 0 !== n ? S.style(e, t, n) : S.css(e, t)
                }, e, t, arguments.length > 1)
            }
        }), S.Tween = et, et.prototype = {
            constructor: et, init: function (e, t, n, i, o, r) {
                this.elem = e, this.prop = n, this.easing = o || S.easing._default, this.options = t, this.start = this.now = this.cur(), this.end = i, this.unit = r || (S.cssNumber[n] ? "" : "px")
            }, cur: function () {
                var e = et.propHooks[this.prop];
                return e && e.get ? e.get(this) : et.propHooks._default.get(this)
            }, run: function (e) {
                var t, n = et.propHooks[this.prop];
                return this.options.duration ? this.pos = t = S.easing[this.easing](e, this.options.duration * e, 0, 1, this.options.duration) : this.pos = t = e, this.now = (this.end - this.start) * t + this.start, this.options.step && this.options.step.call(this.elem, this.now, this), n && n.set ? n.set(this) : et.propHooks._default.set(this), this
            }
        }, et.prototype.init.prototype = et.prototype, et.propHooks = {
            _default: {
                get: function (e) {
                    var t;
                    return 1 !== e.elem.nodeType || null != e.elem[e.prop] && null == e.elem.style[e.prop] ? e.elem[e.prop] : (t = S.css(e.elem, e.prop, "")) && "auto" !== t ? t : 0
                }, set: function (e) {
                    S.fx.step[e.prop] ? S.fx.step[e.prop](e) : 1 !== e.elem.nodeType || null == e.elem.style[S.cssProps[e.prop]] && !S.cssHooks[e.prop] ? e.elem[e.prop] = e.now : S.style(e.elem, e.prop, e.now + e.unit)
                }
            }
        }, et.propHooks.scrollTop = et.propHooks.scrollLeft = {
            set: function (e) {
                e.elem.nodeType && e.elem.parentNode && (e.elem[e.prop] = e.now)
            }
        }, S.easing = {
            linear: function (e) {
                return e
            }, swing: function (e) {
                return .5 - Math.cos(e * Math.PI) / 2
            }, _default: "swing"
        }, S.fx = et.prototype.init, S.fx.step = {};
        var tt, nt, it = /^(?:toggle|show|hide)$/, ot = /queueHooks$/;

        function rt() {
            nt && (!1 === s.hidden && n.requestAnimationFrame ? n.requestAnimationFrame(rt) : n.setTimeout(rt, S.fx.interval), S.fx.tick())
        }

        function st() {
            return n.setTimeout(function () {
                tt = void 0
            }), tt = Date.now()
        }

        function at(e, t) {
            var n, i = 0, o = {height: e};
            for (t = t ? 1 : 0; i < 4; i += 2 - t) o["margin" + (n = re[i])] = o["padding" + n] = e;
            return t && (o.opacity = o.width = e), o
        }

        function lt(e, t, n) {
            for (var i, o = (ct.tweeners[t] || []).concat(ct.tweeners["*"]), r = 0, s = o.length; r < s; r++) if (i = o[r].call(n, t, e)) return i
        }

        function ct(e, t, n) {
            var i, o, r = 0, s = ct.prefilters.length, a = S.Deferred().always(function () {
                delete l.elem
            }), l = function () {
                if (o) return !1;
                for (var t = tt || st(), n = Math.max(0, c.startTime + c.duration - t), i = 1 - (n / c.duration || 0), r = 0, s = c.tweens.length; r < s; r++) c.tweens[r].run(i);
                return a.notifyWith(e, [c, i, n]), i < 1 && s ? n : (s || a.notifyWith(e, [c, 1, 0]), a.resolveWith(e, [c]), !1)
            }, c = a.promise({
                elem: e,
                props: S.extend({}, t),
                opts: S.extend(!0, {specialEasing: {}, easing: S.easing._default}, n),
                originalProperties: t,
                originalOptions: n,
                startTime: tt || st(),
                duration: n.duration,
                tweens: [],
                createTween: function (t, n) {
                    var i = S.Tween(e, c.opts, t, n, c.opts.specialEasing[t] || c.opts.easing);
                    return c.tweens.push(i), i
                },
                stop: function (t) {
                    var n = 0, i = t ? c.tweens.length : 0;
                    if (o) return this;
                    for (o = !0; n < i; n++) c.tweens[n].run(1);
                    return t ? (a.notifyWith(e, [c, 1, 0]), a.resolveWith(e, [c, t])) : a.rejectWith(e, [c, t]), this
                }
            }), u = c.props;
            for (!function (e, t) {
                var n, i, o, r, s;
                for (n in e) if (o = t[i = K(n)], r = e[n], Array.isArray(r) && (o = r[1], r = e[n] = r[0]), n !== i && (e[i] = r, delete e[n]), (s = S.cssHooks[i]) && "expand" in s) for (n in r = s.expand(r), delete e[i], r) n in e || (e[n] = r[n], t[n] = o); else t[i] = o
            }(u, c.opts.specialEasing); r < s; r++) if (i = ct.prefilters[r].call(c, e, u, c.opts)) return y(i.stop) && (S._queueHooks(c.elem, c.opts.queue).stop = i.stop.bind(i)), i;
            return S.map(u, lt, c), y(c.opts.start) && c.opts.start.call(e, c), c.progress(c.opts.progress).done(c.opts.done, c.opts.complete).fail(c.opts.fail).always(c.opts.always), S.fx.timer(S.extend(l, {
                elem: e,
                anim: c,
                queue: c.opts.queue
            })), c
        }

        S.Animation = S.extend(ct, {
            tweeners: {
                "*": [function (e, t) {
                    var n = this.createTween(e, t);
                    return le(n.elem, e, oe.exec(t), n), n
                }]
            }, tweener: function (e, t) {
                y(e) ? (t = e, e = ["*"]) : e = e.match($);
                for (var n, i = 0, o = e.length; i < o; i++) n = e[i], ct.tweeners[n] = ct.tweeners[n] || [], ct.tweeners[n].unshift(t)
            }, prefilters: [function (e, t, n) {
                var i, o, r, s, a, l, c, u, d = "width" in t || "height" in t, p = this, f = {}, h = e.style,
                    m = e.nodeType && se(e), g = X.get(e, "fxshow");
                for (i in n.queue || (null == (s = S._queueHooks(e, "fx")).unqueued && (s.unqueued = 0, a = s.empty.fire, s.empty.fire = function () {
                    s.unqueued || a()
                }), s.unqueued++, p.always(function () {
                    p.always(function () {
                        s.unqueued--, S.queue(e, "fx").length || s.empty.fire()
                    })
                })), t) if (o = t[i], it.test(o)) {
                    if (delete t[i], r = r || "toggle" === o, o === (m ? "hide" : "show")) {
                        if ("show" !== o || !g || void 0 === g[i]) continue;
                        m = !0
                    }
                    f[i] = g && g[i] || S.style(e, i)
                }
                if ((l = !S.isEmptyObject(t)) || !S.isEmptyObject(f)) for (i in d && 1 === e.nodeType && (n.overflow = [h.overflow, h.overflowX, h.overflowY], null == (c = g && g.display) && (c = X.get(e, "display")), "none" === (u = S.css(e, "display")) && (c ? u = c : (de([e], !0), c = e.style.display || c, u = S.css(e, "display"), de([e]))), ("inline" === u || "inline-block" === u && null != c) && "none" === S.css(e, "float") && (l || (p.done(function () {
                    h.display = c
                }), null == c && (u = h.display, c = "none" === u ? "" : u)), h.display = "inline-block")), n.overflow && (h.overflow = "hidden", p.always(function () {
                    h.overflow = n.overflow[0], h.overflowX = n.overflow[1], h.overflowY = n.overflow[2]
                })), l = !1, f) l || (g ? "hidden" in g && (m = g.hidden) : g = X.access(e, "fxshow", {display: c}), r && (g.hidden = !m), m && de([e], !0), p.done(function () {
                    for (i in m || de([e]), X.remove(e, "fxshow"), f) S.style(e, i, f[i])
                })), l = lt(m ? g[i] : 0, i, p), i in g || (g[i] = l.start, m && (l.end = l.start, l.start = 0))
            }], prefilter: function (e, t) {
                t ? ct.prefilters.unshift(e) : ct.prefilters.push(e)
            }
        }), S.speed = function (e, t, n) {
            var i = e && "object" == typeof e ? S.extend({}, e) : {
                complete: n || !n && t || y(e) && e,
                duration: e,
                easing: n && t || t && !y(t) && t
            };
            return S.fx.off ? i.duration = 0 : "number" != typeof i.duration && (i.duration in S.fx.speeds ? i.duration = S.fx.speeds[i.duration] : i.duration = S.fx.speeds._default), null != i.queue && !0 !== i.queue || (i.queue = "fx"), i.old = i.complete, i.complete = function () {
                y(i.old) && i.old.call(this), i.queue && S.dequeue(this, i.queue)
            }, i
        }, S.fn.extend({
            fadeTo: function (e, t, n, i) {
                return this.filter(se).css("opacity", 0).show().end().animate({opacity: t}, e, n, i)
            }, animate: function (e, t, n, i) {
                var o = S.isEmptyObject(e), r = S.speed(t, n, i), s = function () {
                    var t = ct(this, S.extend({}, e), r);
                    (o || X.get(this, "finish")) && t.stop(!0)
                };
                return s.finish = s, o || !1 === r.queue ? this.each(s) : this.queue(r.queue, s)
            }, stop: function (e, t, n) {
                var i = function (e) {
                    var t = e.stop;
                    delete e.stop, t(n)
                };
                return "string" != typeof e && (n = t, t = e, e = void 0), t && !1 !== e && this.queue(e || "fx", []), this.each(function () {
                    var t = !0, o = null != e && e + "queueHooks", r = S.timers, s = X.get(this);
                    if (o) s[o] && s[o].stop && i(s[o]); else for (o in s) s[o] && s[o].stop && ot.test(o) && i(s[o]);
                    for (o = r.length; o--;) r[o].elem !== this || null != e && r[o].queue !== e || (r[o].anim.stop(n), t = !1, r.splice(o, 1));
                    !t && n || S.dequeue(this, e)
                })
            }, finish: function (e) {
                return !1 !== e && (e = e || "fx"), this.each(function () {
                    var t, n = X.get(this), i = n[e + "queue"], o = n[e + "queueHooks"], r = S.timers,
                        s = i ? i.length : 0;
                    for (n.finish = !0, S.queue(this, e, []), o && o.stop && o.stop.call(this, !0), t = r.length; t--;) r[t].elem === this && r[t].queue === e && (r[t].anim.stop(!0), r.splice(t, 1));
                    for (t = 0; t < s; t++) i[t] && i[t].finish && i[t].finish.call(this);
                    delete n.finish
                })
            }
        }), S.each(["toggle", "show", "hide"], function (e, t) {
            var n = S.fn[t];
            S.fn[t] = function (e, i, o) {
                return null == e || "boolean" == typeof e ? n.apply(this, arguments) : this.animate(at(t, !0), e, i, o)
            }
        }), S.each({
            slideDown: at("show"),
            slideUp: at("hide"),
            slideToggle: at("toggle"),
            fadeIn: {opacity: "show"},
            fadeOut: {opacity: "hide"},
            fadeToggle: {opacity: "toggle"}
        }, function (e, t) {
            S.fn[e] = function (e, n, i) {
                return this.animate(t, e, n, i)
            }
        }), S.timers = [], S.fx.tick = function () {
            var e, t = 0, n = S.timers;
            for (tt = Date.now(); t < n.length; t++) (e = n[t])() || n[t] !== e || n.splice(t--, 1);
            n.length || S.fx.stop(), tt = void 0
        }, S.fx.timer = function (e) {
            S.timers.push(e), S.fx.start()
        }, S.fx.interval = 13, S.fx.start = function () {
            nt || (nt = !0, rt())
        }, S.fx.stop = function () {
            nt = null
        }, S.fx.speeds = {slow: 600, fast: 200, _default: 400}, S.fn.delay = function (e, t) {
            return e = S.fx && S.fx.speeds[e] || e, t = t || "fx", this.queue(t, function (t, i) {
                var o = n.setTimeout(t, e);
                i.stop = function () {
                    n.clearTimeout(o)
                }
            })
        }, function () {
            var e = s.createElement("input"), t = s.createElement("select").appendChild(s.createElement("option"));
            e.type = "checkbox", v.checkOn = "" !== e.value, v.optSelected = t.selected, (e = s.createElement("input")).value = "t", e.type = "radio", v.radioValue = "t" === e.value
        }();
        var ut, dt = S.expr.attrHandle;
        S.fn.extend({
            attr: function (e, t) {
                return B(this, S.attr, e, t, arguments.length > 1)
            }, removeAttr: function (e) {
                return this.each(function () {
                    S.removeAttr(this, e)
                })
            }
        }), S.extend({
            attr: function (e, t, n) {
                var i, o, r = e.nodeType;
                if (3 !== r && 8 !== r && 2 !== r) return void 0 === e.getAttribute ? S.prop(e, t, n) : (1 === r && S.isXMLDoc(e) || (o = S.attrHooks[t.toLowerCase()] || (S.expr.match.bool.test(t) ? ut : void 0)), void 0 !== n ? null === n ? void S.removeAttr(e, t) : o && "set" in o && void 0 !== (i = o.set(e, n, t)) ? i : (e.setAttribute(t, n + ""), n) : o && "get" in o && null !== (i = o.get(e, t)) ? i : null == (i = S.find.attr(e, t)) ? void 0 : i)
            }, attrHooks: {
                type: {
                    set: function (e, t) {
                        if (!v.radioValue && "radio" === t && O(e, "input")) {
                            var n = e.value;
                            return e.setAttribute("type", t), n && (e.value = n), t
                        }
                    }
                }
            }, removeAttr: function (e, t) {
                var n, i = 0, o = t && t.match($);
                if (o && 1 === e.nodeType) for (; n = o[i++];) e.removeAttribute(n)
            }
        }), ut = {
            set: function (e, t, n) {
                return !1 === t ? S.removeAttr(e, n) : e.setAttribute(n, n), n
            }
        }, S.each(S.expr.match.bool.source.match(/\w+/g), function (e, t) {
            var n = dt[t] || S.find.attr;
            dt[t] = function (e, t, i) {
                var o, r, s = t.toLowerCase();
                return i || (r = dt[s], dt[s] = o, o = null != n(e, t, i) ? s : null, dt[s] = r), o
            }
        });
        var pt = /^(?:input|select|textarea|button)$/i, ft = /^(?:a|area)$/i;

        function ht(e) {
            return (e.match($) || []).join(" ")
        }

        function mt(e) {
            return e.getAttribute && e.getAttribute("class") || ""
        }

        function gt(e) {
            return Array.isArray(e) ? e : "string" == typeof e && e.match($) || []
        }

        S.fn.extend({
            prop: function (e, t) {
                return B(this, S.prop, e, t, arguments.length > 1)
            }, removeProp: function (e) {
                return this.each(function () {
                    delete this[S.propFix[e] || e]
                })
            }
        }), S.extend({
            prop: function (e, t, n) {
                var i, o, r = e.nodeType;
                if (3 !== r && 8 !== r && 2 !== r) return 1 === r && S.isXMLDoc(e) || (t = S.propFix[t] || t, o = S.propHooks[t]), void 0 !== n ? o && "set" in o && void 0 !== (i = o.set(e, n, t)) ? i : e[t] = n : o && "get" in o && null !== (i = o.get(e, t)) ? i : e[t]
            }, propHooks: {
                tabIndex: {
                    get: function (e) {
                        var t = S.find.attr(e, "tabindex");
                        return t ? parseInt(t, 10) : pt.test(e.nodeName) || ft.test(e.nodeName) && e.href ? 0 : -1
                    }
                }
            }, propFix: {for: "htmlFor", class: "className"}
        }), v.optSelected || (S.propHooks.selected = {
            get: function (e) {
                var t = e.parentNode;
                return t && t.parentNode && t.parentNode.selectedIndex, null
            }, set: function (e) {
                var t = e.parentNode;
                t && (t.selectedIndex, t.parentNode && t.parentNode.selectedIndex)
            }
        }), S.each(["tabIndex", "readOnly", "maxLength", "cellSpacing", "cellPadding", "rowSpan", "colSpan", "useMap", "frameBorder", "contentEditable"], function () {
            S.propFix[this.toLowerCase()] = this
        }), S.fn.extend({
            addClass: function (e) {
                var t, n, i, o, r, s, a, l = 0;
                if (y(e)) return this.each(function (t) {
                    S(this).addClass(e.call(this, t, mt(this)))
                });
                if ((t = gt(e)).length) for (; n = this[l++];) if (o = mt(n), i = 1 === n.nodeType && " " + ht(o) + " ") {
                    for (s = 0; r = t[s++];) i.indexOf(" " + r + " ") < 0 && (i += r + " ");
                    o !== (a = ht(i)) && n.setAttribute("class", a)
                }
                return this
            }, removeClass: function (e) {
                var t, n, i, o, r, s, a, l = 0;
                if (y(e)) return this.each(function (t) {
                    S(this).removeClass(e.call(this, t, mt(this)))
                });
                if (!arguments.length) return this.attr("class", "");
                if ((t = gt(e)).length) for (; n = this[l++];) if (o = mt(n), i = 1 === n.nodeType && " " + ht(o) + " ") {
                    for (s = 0; r = t[s++];) for (; i.indexOf(" " + r + " ") > -1;) i = i.replace(" " + r + " ", " ");
                    o !== (a = ht(i)) && n.setAttribute("class", a)
                }
                return this
            }, toggleClass: function (e, t) {
                var n = typeof e, i = "string" === n || Array.isArray(e);
                return "boolean" == typeof t && i ? t ? this.addClass(e) : this.removeClass(e) : y(e) ? this.each(function (n) {
                    S(this).toggleClass(e.call(this, n, mt(this), t), t)
                }) : this.each(function () {
                    var t, o, r, s;
                    if (i) for (o = 0, r = S(this), s = gt(e); t = s[o++];) r.hasClass(t) ? r.removeClass(t) : r.addClass(t); else void 0 !== e && "boolean" !== n || ((t = mt(this)) && X.set(this, "__className__", t), this.setAttribute && this.setAttribute("class", t || !1 === e ? "" : X.get(this, "__className__") || ""))
                })
            }, hasClass: function (e) {
                var t, n, i = 0;
                for (t = " " + e + " "; n = this[i++];) if (1 === n.nodeType && (" " + ht(mt(n)) + " ").indexOf(t) > -1) return !0;
                return !1
            }
        });
        var vt = /\r/g;
        S.fn.extend({
            val: function (e) {
                var t, n, i, o = this[0];
                return arguments.length ? (i = y(e), this.each(function (n) {
                    var o;
                    1 === this.nodeType && (null == (o = i ? e.call(this, n, S(this).val()) : e) ? o = "" : "number" == typeof o ? o += "" : Array.isArray(o) && (o = S.map(o, function (e) {
                        return null == e ? "" : e + ""
                    })), (t = S.valHooks[this.type] || S.valHooks[this.nodeName.toLowerCase()]) && "set" in t && void 0 !== t.set(this, o, "value") || (this.value = o))
                })) : o ? (t = S.valHooks[o.type] || S.valHooks[o.nodeName.toLowerCase()]) && "get" in t && void 0 !== (n = t.get(o, "value")) ? n : "string" == typeof (n = o.value) ? n.replace(vt, "") : null == n ? "" : n : void 0
            }
        }), S.extend({
            valHooks: {
                option: {
                    get: function (e) {
                        var t = S.find.attr(e, "value");
                        return null != t ? t : ht(S.text(e))
                    }
                }, select: {
                    get: function (e) {
                        var t, n, i, o = e.options, r = e.selectedIndex, s = "select-one" === e.type, a = s ? null : [],
                            l = s ? r + 1 : o.length;
                        for (i = r < 0 ? l : s ? r : 0; i < l; i++) if (((n = o[i]).selected || i === r) && !n.disabled && (!n.parentNode.disabled || !O(n.parentNode, "optgroup"))) {
                            if (t = S(n).val(), s) return t;
                            a.push(t)
                        }
                        return a
                    }, set: function (e, t) {
                        for (var n, i, o = e.options, r = S.makeArray(t), s = o.length; s--;) ((i = o[s]).selected = S.inArray(S.valHooks.option.get(i), r) > -1) && (n = !0);
                        return n || (e.selectedIndex = -1), r
                    }
                }
            }
        }), S.each(["radio", "checkbox"], function () {
            S.valHooks[this] = {
                set: function (e, t) {
                    if (Array.isArray(t)) return e.checked = S.inArray(S(e).val(), t) > -1
                }
            }, v.checkOn || (S.valHooks[this].get = function (e) {
                return null === e.getAttribute("value") ? "on" : e.value
            })
        }), v.focusin = "onfocusin" in n;
        var yt = /^(?:focusinfocus|focusoutblur)$/, bt = function (e) {
            e.stopPropagation()
        };
        S.extend(S.event, {
            trigger: function (e, t, i, o) {
                var r, a, l, c, u, d, p, f, m = [i || s], g = h.call(e, "type") ? e.type : e,
                    v = h.call(e, "namespace") ? e.namespace.split(".") : [];
                if (a = f = l = i = i || s, 3 !== i.nodeType && 8 !== i.nodeType && !yt.test(g + S.event.triggered) && (g.indexOf(".") > -1 && (g = (v = g.split(".")).shift(), v.sort()), u = g.indexOf(":") < 0 && "on" + g, (e = e[S.expando] ? e : new S.Event(g, "object" == typeof e && e)).isTrigger = o ? 2 : 3, e.namespace = v.join("."), e.rnamespace = e.namespace ? new RegExp("(^|\\.)" + v.join("\\.(?:.*\\.|)") + "(\\.|$)") : null, e.result = void 0, e.target || (e.target = i), t = null == t ? [e] : S.makeArray(t, [e]), p = S.event.special[g] || {}, o || !p.trigger || !1 !== p.trigger.apply(i, t))) {
                    if (!o && !p.noBubble && !b(i)) {
                        for (c = p.delegateType || g, yt.test(c + g) || (a = a.parentNode); a; a = a.parentNode) m.push(a), l = a;
                        l === (i.ownerDocument || s) && m.push(l.defaultView || l.parentWindow || n)
                    }
                    for (r = 0; (a = m[r++]) && !e.isPropagationStopped();) f = a, e.type = r > 1 ? c : p.bindType || g, (d = (X.get(a, "events") || {})[e.type] && X.get(a, "handle")) && d.apply(a, t), (d = u && a[u]) && d.apply && Q(a) && (e.result = d.apply(a, t), !1 === e.result && e.preventDefault());
                    return e.type = g, o || e.isDefaultPrevented() || p._default && !1 !== p._default.apply(m.pop(), t) || !Q(i) || u && y(i[g]) && !b(i) && ((l = i[u]) && (i[u] = null), S.event.triggered = g, e.isPropagationStopped() && f.addEventListener(g, bt), i[g](), e.isPropagationStopped() && f.removeEventListener(g, bt), S.event.triggered = void 0, l && (i[u] = l)), e.result
                }
            }, simulate: function (e, t, n) {
                var i = S.extend(new S.Event, n, {type: e, isSimulated: !0});
                S.event.trigger(i, null, t)
            }
        }), S.fn.extend({
            trigger: function (e, t) {
                return this.each(function () {
                    S.event.trigger(e, t, this)
                })
            }, triggerHandler: function (e, t) {
                var n = this[0];
                if (n) return S.event.trigger(e, t, n, !0)
            }
        }), v.focusin || S.each({focus: "focusin", blur: "focusout"}, function (e, t) {
            var n = function (e) {
                S.event.simulate(t, e.target, S.event.fix(e))
            };
            S.event.special[t] = {
                setup: function () {
                    var i = this.ownerDocument || this, o = X.access(i, t);
                    o || i.addEventListener(e, n, !0), X.access(i, t, (o || 0) + 1)
                }, teardown: function () {
                    var i = this.ownerDocument || this, o = X.access(i, t) - 1;
                    o ? X.access(i, t, o) : (i.removeEventListener(e, n, !0), X.remove(i, t))
                }
            }
        });
        var wt = n.location, xt = Date.now(), Tt = /\?/;
        S.parseXML = function (e) {
            var t;
            if (!e || "string" != typeof e) return null;
            try {
                t = (new n.DOMParser).parseFromString(e, "text/xml")
            } catch (e) {
                t = void 0
            }
            return t && !t.getElementsByTagName("parsererror").length || S.error("Invalid XML: " + e), t
        };
        var St = /\[\]$/, Ct = /\r?\n/g, Et = /^(?:submit|button|image|reset|file)$/i,
            kt = /^(?:input|select|textarea|keygen)/i;

        function _t(e, t, n, i) {
            var o;
            if (Array.isArray(t)) S.each(t, function (t, o) {
                n || St.test(e) ? i(e, o) : _t(e + "[" + ("object" == typeof o && null != o ? t : "") + "]", o, n, i)
            }); else if (n || "object" !== T(t)) i(e, t); else for (o in t) _t(e + "[" + o + "]", t[o], n, i)
        }

        S.param = function (e, t) {
            var n, i = [], o = function (e, t) {
                var n = y(t) ? t() : t;
                i[i.length] = encodeURIComponent(e) + "=" + encodeURIComponent(null == n ? "" : n)
            };
            if (Array.isArray(e) || e.jquery && !S.isPlainObject(e)) S.each(e, function () {
                o(this.name, this.value)
            }); else for (n in e) _t(n, e[n], t, o);
            return i.join("&")
        }, S.fn.extend({
            serialize: function () {
                return S.param(this.serializeArray())
            }, serializeArray: function () {
                return this.map(function () {
                    var e = S.prop(this, "elements");
                    return e ? S.makeArray(e) : this
                }).filter(function () {
                    var e = this.type;
                    return this.name && !S(this).is(":disabled") && kt.test(this.nodeName) && !Et.test(e) && (this.checked || !pe.test(e))
                }).map(function (e, t) {
                    var n = S(this).val();
                    return null == n ? null : Array.isArray(n) ? S.map(n, function (e) {
                        return {name: t.name, value: e.replace(Ct, "\r\n")}
                    }) : {name: t.name, value: n.replace(Ct, "\r\n")}
                }).get()
            }
        });
        var At = /%20/g, It = /#.*$/, Ot = /([?&])_=[^&]*/, Dt = /^(.*?):[ \t]*([^\r\n]*)$/gm, Lt = /^(?:GET|HEAD)$/,
            Nt = /^\/\//, Pt = {}, Mt = {}, jt = "*/".concat("*"), Ht = s.createElement("a");

        function $t(e) {
            return function (t, n) {
                "string" != typeof t && (n = t, t = "*");
                var i, o = 0, r = t.toLowerCase().match($) || [];
                if (y(n)) for (; i = r[o++];) "+" === i[0] ? (i = i.slice(1) || "*", (e[i] = e[i] || []).unshift(n)) : (e[i] = e[i] || []).push(n)
            }
        }

        function Ft(e, t, n, i) {
            var o = {}, r = e === Mt;

            function s(a) {
                var l;
                return o[a] = !0, S.each(e[a] || [], function (e, a) {
                    var c = a(t, n, i);
                    return "string" != typeof c || r || o[c] ? r ? !(l = c) : void 0 : (t.dataTypes.unshift(c), s(c), !1)
                }), l
            }

            return s(t.dataTypes[0]) || !o["*"] && s("*")
        }

        function Rt(e, t) {
            var n, i, o = S.ajaxSettings.flatOptions || {};
            for (n in t) void 0 !== t[n] && ((o[n] ? e : i || (i = {}))[n] = t[n]);
            return i && S.extend(!0, e, i), e
        }

        Ht.href = wt.href, S.extend({
            active: 0,
            lastModified: {},
            etag: {},
            ajaxSettings: {
                url: wt.href,
                type: "GET",
                isLocal: /^(?:about|app|app-storage|.+-extension|file|res|widget):$/.test(wt.protocol),
                global: !0,
                processData: !0,
                async: !0,
                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                accepts: {
                    "*": jt,
                    text: "text/plain",
                    html: "text/html",
                    xml: "application/xml, text/xml",
                    json: "application/json, text/javascript"
                },
                contents: {xml: /\bxml\b/, html: /\bhtml/, json: /\bjson\b/},
                responseFields: {xml: "responseXML", text: "responseText", json: "responseJSON"},
                converters: {"* text": String, "text html": !0, "text json": JSON.parse, "text xml": S.parseXML},
                flatOptions: {url: !0, context: !0}
            },
            ajaxSetup: function (e, t) {
                return t ? Rt(Rt(e, S.ajaxSettings), t) : Rt(S.ajaxSettings, e)
            },
            ajaxPrefilter: $t(Pt),
            ajaxTransport: $t(Mt),
            ajax: function (e, t) {
                "object" == typeof e && (t = e, e = void 0), t = t || {};
                var i, o, r, a, l, c, u, d, p, f, h = S.ajaxSetup({}, t), m = h.context || h,
                    g = h.context && (m.nodeType || m.jquery) ? S(m) : S.event, v = S.Deferred(),
                    y = S.Callbacks("once memory"), b = h.statusCode || {}, w = {}, x = {}, T = "canceled", C = {
                        readyState: 0, getResponseHeader: function (e) {
                            var t;
                            if (u) {
                                if (!a) for (a = {}; t = Dt.exec(r);) a[t[1].toLowerCase()] = t[2];
                                t = a[e.toLowerCase()]
                            }
                            return null == t ? null : t
                        }, getAllResponseHeaders: function () {
                            return u ? r : null
                        }, setRequestHeader: function (e, t) {
                            return null == u && (e = x[e.toLowerCase()] = x[e.toLowerCase()] || e, w[e] = t), this
                        }, overrideMimeType: function (e) {
                            return null == u && (h.mimeType = e), this
                        }, statusCode: function (e) {
                            var t;
                            if (e) if (u) C.always(e[C.status]); else for (t in e) b[t] = [b[t], e[t]];
                            return this
                        }, abort: function (e) {
                            var t = e || T;
                            return i && i.abort(t), E(0, t), this
                        }
                    };
                if (v.promise(C), h.url = ((e || h.url || wt.href) + "").replace(Nt, wt.protocol + "//"), h.type = t.method || t.type || h.method || h.type, h.dataTypes = (h.dataType || "*").toLowerCase().match($) || [""], null == h.crossDomain) {
                    c = s.createElement("a");
                    try {
                        c.href = h.url, c.href = c.href, h.crossDomain = Ht.protocol + "//" + Ht.host != c.protocol + "//" + c.host
                    } catch (e) {
                        h.crossDomain = !0
                    }
                }
                if (h.data && h.processData && "string" != typeof h.data && (h.data = S.param(h.data, h.traditional)), Ft(Pt, h, t, C), u) return C;
                for (p in (d = S.event && h.global) && 0 == S.active++ && S.event.trigger("ajaxStart"), h.type = h.type.toUpperCase(), h.hasContent = !Lt.test(h.type), o = h.url.replace(It, ""), h.hasContent ? h.data && h.processData && 0 === (h.contentType || "").indexOf("application/x-www-form-urlencoded") && (h.data = h.data.replace(At, "+")) : (f = h.url.slice(o.length), h.data && (h.processData || "string" == typeof h.data) && (o += (Tt.test(o) ? "&" : "?") + h.data, delete h.data), !1 === h.cache && (o = o.replace(Ot, "$1"), f = (Tt.test(o) ? "&" : "?") + "_=" + xt++ + f), h.url = o + f), h.ifModified && (S.lastModified[o] && C.setRequestHeader("If-Modified-Since", S.lastModified[o]), S.etag[o] && C.setRequestHeader("If-None-Match", S.etag[o])), (h.data && h.hasContent && !1 !== h.contentType || t.contentType) && C.setRequestHeader("Content-Type", h.contentType), C.setRequestHeader("Accept", h.dataTypes[0] && h.accepts[h.dataTypes[0]] ? h.accepts[h.dataTypes[0]] + ("*" !== h.dataTypes[0] ? ", " + jt + "; q=0.01" : "") : h.accepts["*"]), h.headers) C.setRequestHeader(p, h.headers[p]);
                if (h.beforeSend && (!1 === h.beforeSend.call(m, C, h) || u)) return C.abort();
                if (T = "abort", y.add(h.complete), C.done(h.success), C.fail(h.error), i = Ft(Mt, h, t, C)) {
                    if (C.readyState = 1, d && g.trigger("ajaxSend", [C, h]), u) return C;
                    h.async && h.timeout > 0 && (l = n.setTimeout(function () {
                        C.abort("timeout")
                    }, h.timeout));
                    try {
                        u = !1, i.send(w, E)
                    } catch (e) {
                        if (u) throw e;
                        E(-1, e)
                    }
                } else E(-1, "No Transport");

                function E(e, t, s, a) {
                    var c, p, f, w, x, T = t;
                    u || (u = !0, l && n.clearTimeout(l), i = void 0, r = a || "", C.readyState = e > 0 ? 4 : 0, c = e >= 200 && e < 300 || 304 === e, s && (w = function (e, t, n) {
                        for (var i, o, r, s, a = e.contents, l = e.dataTypes; "*" === l[0];) l.shift(), void 0 === i && (i = e.mimeType || t.getResponseHeader("Content-Type"));
                        if (i) for (o in a) if (a[o] && a[o].test(i)) {
                            l.unshift(o);
                            break
                        }
                        if (l[0] in n) r = l[0]; else {
                            for (o in n) {
                                if (!l[0] || e.converters[o + " " + l[0]]) {
                                    r = o;
                                    break
                                }
                                s || (s = o)
                            }
                            r = r || s
                        }
                        if (r) return r !== l[0] && l.unshift(r), n[r]
                    }(h, C, s)), w = function (e, t, n, i) {
                        var o, r, s, a, l, c = {}, u = e.dataTypes.slice();
                        if (u[1]) for (s in e.converters) c[s.toLowerCase()] = e.converters[s];
                        for (r = u.shift(); r;) if (e.responseFields[r] && (n[e.responseFields[r]] = t), !l && i && e.dataFilter && (t = e.dataFilter(t, e.dataType)), l = r, r = u.shift()) if ("*" === r) r = l; else if ("*" !== l && l !== r) {
                            if (!(s = c[l + " " + r] || c["* " + r])) for (o in c) if ((a = o.split(" "))[1] === r && (s = c[l + " " + a[0]] || c["* " + a[0]])) {
                                !0 === s ? s = c[o] : !0 !== c[o] && (r = a[0], u.unshift(a[1]));
                                break
                            }
                            if (!0 !== s) if (s && e.throws) t = s(t); else try {
                                t = s(t)
                            } catch (e) {
                                return {state: "parsererror", error: s ? e : "No conversion from " + l + " to " + r}
                            }
                        }
                        return {state: "success", data: t}
                    }(h, w, C, c), c ? (h.ifModified && ((x = C.getResponseHeader("Last-Modified")) && (S.lastModified[o] = x), (x = C.getResponseHeader("etag")) && (S.etag[o] = x)), 204 === e || "HEAD" === h.type ? T = "nocontent" : 304 === e ? T = "notmodified" : (T = w.state, p = w.data, c = !(f = w.error))) : (f = T, !e && T || (T = "error", e < 0 && (e = 0))), C.status = e, C.statusText = (t || T) + "", c ? v.resolveWith(m, [p, T, C]) : v.rejectWith(m, [C, T, f]), C.statusCode(b), b = void 0, d && g.trigger(c ? "ajaxSuccess" : "ajaxError", [C, h, c ? p : f]), y.fireWith(m, [C, T]), d && (g.trigger("ajaxComplete", [C, h]), --S.active || S.event.trigger("ajaxStop")))
                }

                return C
            },
            getJSON: function (e, t, n) {
                return S.get(e, t, n, "json")
            },
            getScript: function (e, t) {
                return S.get(e, void 0, t, "script")
            }
        }), S.each(["get", "post"], function (e, t) {
            S[t] = function (e, n, i, o) {
                return y(n) && (o = o || i, i = n, n = void 0), S.ajax(S.extend({
                    url: e,
                    type: t,
                    dataType: o,
                    data: n,
                    success: i
                }, S.isPlainObject(e) && e))
            }
        }), S._evalUrl = function (e) {
            return S.ajax({url: e, type: "GET", dataType: "script", cache: !0, async: !1, global: !1, throws: !0})
        }, S.fn.extend({
            wrapAll: function (e) {
                var t;
                return this[0] && (y(e) && (e = e.call(this[0])), t = S(e, this[0].ownerDocument).eq(0).clone(!0), this[0].parentNode && t.insertBefore(this[0]), t.map(function () {
                    for (var e = this; e.firstElementChild;) e = e.firstElementChild;
                    return e
                }).append(this)), this
            }, wrapInner: function (e) {
                return y(e) ? this.each(function (t) {
                    S(this).wrapInner(e.call(this, t))
                }) : this.each(function () {
                    var t = S(this), n = t.contents();
                    n.length ? n.wrapAll(e) : t.append(e)
                })
            }, wrap: function (e) {
                var t = y(e);
                return this.each(function (n) {
                    S(this).wrapAll(t ? e.call(this, n) : e)
                })
            }, unwrap: function (e) {
                return this.parent(e).not("body").each(function () {
                    S(this).replaceWith(this.childNodes)
                }), this
            }
        }), S.expr.pseudos.hidden = function (e) {
            return !S.expr.pseudos.visible(e)
        }, S.expr.pseudos.visible = function (e) {
            return !!(e.offsetWidth || e.offsetHeight || e.getClientRects().length)
        }, S.ajaxSettings.xhr = function () {
            try {
                return new n.XMLHttpRequest
            } catch (e) {
            }
        };
        var Wt = {0: 200, 1223: 204}, Vt = S.ajaxSettings.xhr();
        v.cors = !!Vt && "withCredentials" in Vt, v.ajax = Vt = !!Vt, S.ajaxTransport(function (e) {
            var t, i;
            if (v.cors || Vt && !e.crossDomain) return {
                send: function (o, r) {
                    var s, a = e.xhr();
                    if (a.open(e.type, e.url, e.async, e.username, e.password), e.xhrFields) for (s in e.xhrFields) a[s] = e.xhrFields[s];
                    for (s in e.mimeType && a.overrideMimeType && a.overrideMimeType(e.mimeType), e.crossDomain || o["X-Requested-With"] || (o["X-Requested-With"] = "XMLHttpRequest"), o) a.setRequestHeader(s, o[s]);
                    t = function (e) {
                        return function () {
                            t && (t = i = a.onload = a.onerror = a.onabort = a.ontimeout = a.onreadystatechange = null, "abort" === e ? a.abort() : "error" === e ? "number" != typeof a.status ? r(0, "error") : r(a.status, a.statusText) : r(Wt[a.status] || a.status, a.statusText, "text" !== (a.responseType || "text") || "string" != typeof a.responseText ? {binary: a.response} : {text: a.responseText}, a.getAllResponseHeaders()))
                        }
                    }, a.onload = t(), i = a.onerror = a.ontimeout = t("error"), void 0 !== a.onabort ? a.onabort = i : a.onreadystatechange = function () {
                        4 === a.readyState && n.setTimeout(function () {
                            t && i()
                        })
                    }, t = t("abort");
                    try {
                        a.send(e.hasContent && e.data || null)
                    } catch (e) {
                        if (t) throw e
                    }
                }, abort: function () {
                    t && t()
                }
            }
        }), S.ajaxPrefilter(function (e) {
            e.crossDomain && (e.contents.script = !1)
        }), S.ajaxSetup({
            accepts: {script: "text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},
            contents: {script: /\b(?:java|ecma)script\b/},
            converters: {
                "text script": function (e) {
                    return S.globalEval(e), e
                }
            }
        }), S.ajaxPrefilter("script", function (e) {
            void 0 === e.cache && (e.cache = !1), e.crossDomain && (e.type = "GET")
        }), S.ajaxTransport("script", function (e) {
            var t, n;
            if (e.crossDomain) return {
                send: function (i, o) {
                    t = S("<script>").prop({charset: e.scriptCharset, src: e.url}).on("load error", n = function (e) {
                        t.remove(), n = null, e && o("error" === e.type ? 404 : 200, e.type)
                    }), s.head.appendChild(t[0])
                }, abort: function () {
                    n && n()
                }
            }
        });
        var zt = [], qt = /(=)\?(?=&|$)|\?\?/;
        S.ajaxSetup({
            jsonp: "callback", jsonpCallback: function () {
                var e = zt.pop() || S.expando + "_" + xt++;
                return this[e] = !0, e
            }
        }), S.ajaxPrefilter("json jsonp", function (e, t, i) {
            var o, r, s,
                a = !1 !== e.jsonp && (qt.test(e.url) ? "url" : "string" == typeof e.data && 0 === (e.contentType || "").indexOf("application/x-www-form-urlencoded") && qt.test(e.data) && "data");
            if (a || "jsonp" === e.dataTypes[0]) return o = e.jsonpCallback = y(e.jsonpCallback) ? e.jsonpCallback() : e.jsonpCallback, a ? e[a] = e[a].replace(qt, "$1" + o) : !1 !== e.jsonp && (e.url += (Tt.test(e.url) ? "&" : "?") + e.jsonp + "=" + o), e.converters["script json"] = function () {
                return s || S.error(o + " was not called"), s[0]
            }, e.dataTypes[0] = "json", r = n[o], n[o] = function () {
                s = arguments
            }, i.always(function () {
                void 0 === r ? S(n).removeProp(o) : n[o] = r, e[o] && (e.jsonpCallback = t.jsonpCallback, zt.push(o)), s && y(r) && r(s[0]), s = r = void 0
            }), "script"
        }), v.createHTMLDocument = function () {
            var e = s.implementation.createHTMLDocument("").body;
            return e.innerHTML = "<form></form><form></form>", 2 === e.childNodes.length
        }(), S.parseHTML = function (e, t, n) {
            return "string" != typeof e ? [] : ("boolean" == typeof t && (n = t, t = !1), t || (v.createHTMLDocument ? ((i = (t = s.implementation.createHTMLDocument("")).createElement("base")).href = s.location.href, t.head.appendChild(i)) : t = s), o = D.exec(e), r = !n && [], o ? [t.createElement(o[1])] : (o = be([e], t, r), r && r.length && S(r).remove(), S.merge([], o.childNodes)));
            var i, o, r
        }, S.fn.load = function (e, t, n) {
            var i, o, r, s = this, a = e.indexOf(" ");
            return a > -1 && (i = ht(e.slice(a)), e = e.slice(0, a)), y(t) ? (n = t, t = void 0) : t && "object" == typeof t && (o = "POST"), s.length > 0 && S.ajax({
                url: e,
                type: o || "GET",
                dataType: "html",
                data: t
            }).done(function (e) {
                r = arguments, s.html(i ? S("<div>").append(S.parseHTML(e)).find(i) : e)
            }).always(n && function (e, t) {
                s.each(function () {
                    n.apply(this, r || [e.responseText, t, e])
                })
            }), this
        }, S.each(["ajaxStart", "ajaxStop", "ajaxComplete", "ajaxError", "ajaxSuccess", "ajaxSend"], function (e, t) {
            S.fn[t] = function (e) {
                return this.on(t, e)
            }
        }), S.expr.pseudos.animated = function (e) {
            return S.grep(S.timers, function (t) {
                return e === t.elem
            }).length
        }, S.offset = {
            setOffset: function (e, t, n) {
                var i, o, r, s, a, l, c = S.css(e, "position"), u = S(e), d = {};
                "static" === c && (e.style.position = "relative"), a = u.offset(), r = S.css(e, "top"), l = S.css(e, "left"), ("absolute" === c || "fixed" === c) && (r + l).indexOf("auto") > -1 ? (s = (i = u.position()).top, o = i.left) : (s = parseFloat(r) || 0, o = parseFloat(l) || 0), y(t) && (t = t.call(e, n, S.extend({}, a))), null != t.top && (d.top = t.top - a.top + s), null != t.left && (d.left = t.left - a.left + o), "using" in t ? t.using.call(e, d) : u.css(d)
            }
        }, S.fn.extend({
            offset: function (e) {
                if (arguments.length) return void 0 === e ? this : this.each(function (t) {
                    S.offset.setOffset(this, e, t)
                });
                var t, n, i = this[0];
                return i ? i.getClientRects().length ? (t = i.getBoundingClientRect(), n = i.ownerDocument.defaultView, {
                    top: t.top + n.pageYOffset,
                    left: t.left + n.pageXOffset
                }) : {top: 0, left: 0} : void 0
            }, position: function () {
                if (this[0]) {
                    var e, t, n, i = this[0], o = {top: 0, left: 0};
                    if ("fixed" === S.css(i, "position")) t = i.getBoundingClientRect(); else {
                        for (t = this.offset(), n = i.ownerDocument, e = i.offsetParent || n.documentElement; e && (e === n.body || e === n.documentElement) && "static" === S.css(e, "position");) e = e.parentNode;
                        e && e !== i && 1 === e.nodeType && ((o = S(e).offset()).top += S.css(e, "borderTopWidth", !0), o.left += S.css(e, "borderLeftWidth", !0))
                    }
                    return {
                        top: t.top - o.top - S.css(i, "marginTop", !0),
                        left: t.left - o.left - S.css(i, "marginLeft", !0)
                    }
                }
            }, offsetParent: function () {
                return this.map(function () {
                    for (var e = this.offsetParent; e && "static" === S.css(e, "position");) e = e.offsetParent;
                    return e || we
                })
            }
        }), S.each({scrollLeft: "pageXOffset", scrollTop: "pageYOffset"}, function (e, t) {
            var n = "pageYOffset" === t;
            S.fn[e] = function (i) {
                return B(this, function (e, i, o) {
                    var r;
                    if (b(e) ? r = e : 9 === e.nodeType && (r = e.defaultView), void 0 === o) return r ? r[t] : e[i];
                    r ? r.scrollTo(n ? r.pageXOffset : o, n ? o : r.pageYOffset) : e[i] = o
                }, e, i, arguments.length)
            }
        }), S.each(["top", "left"], function (e, t) {
            S.cssHooks[t] = ze(v.pixelPosition, function (e, n) {
                if (n) return n = Ve(e, t), Fe.test(n) ? S(e).position()[t] + "px" : n
            })
        }), S.each({Height: "height", Width: "width"}, function (e, t) {
            S.each({padding: "inner" + e, content: t, "": "outer" + e}, function (n, i) {
                S.fn[i] = function (o, r) {
                    var s = arguments.length && (n || "boolean" != typeof o),
                        a = n || (!0 === o || !0 === r ? "margin" : "border");
                    return B(this, function (t, n, o) {
                        var r;
                        return b(t) ? 0 === i.indexOf("outer") ? t["inner" + e] : t.document.documentElement["client" + e] : 9 === t.nodeType ? (r = t.documentElement, Math.max(t.body["scroll" + e], r["scroll" + e], t.body["offset" + e], r["offset" + e], r["client" + e])) : void 0 === o ? S.css(t, n, a) : S.style(t, n, o, a)
                    }, t, s ? o : void 0, s)
                }
            })
        }), S.each("blur focus focusin focusout resize scroll click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup contextmenu".split(" "), function (e, t) {
            S.fn[t] = function (e, n) {
                return arguments.length > 0 ? this.on(t, null, e, n) : this.trigger(t)
            }
        }), S.fn.extend({
            hover: function (e, t) {
                return this.mouseenter(e).mouseleave(t || e)
            }
        }), S.fn.extend({
            bind: function (e, t, n) {
                return this.on(e, null, t, n)
            }, unbind: function (e, t) {
                return this.off(e, null, t)
            }, delegate: function (e, t, n, i) {
                return this.on(t, e, n, i)
            }, undelegate: function (e, t, n) {
                return 1 === arguments.length ? this.off(e, "**") : this.off(t, e || "**", n)
            }
        }), S.proxy = function (e, t) {
            var n, i, o;
            if ("string" == typeof t && (n = e[t], t = e, e = n), y(e)) return i = l.call(arguments, 2), (o = function () {
                return e.apply(t || this, i.concat(l.call(arguments)))
            }).guid = e.guid = e.guid || S.guid++, o
        }, S.holdReady = function (e) {
            e ? S.readyWait++ : S.ready(!0)
        }, S.isArray = Array.isArray, S.parseJSON = JSON.parse, S.nodeName = O, S.isFunction = y, S.isWindow = b, S.camelCase = K, S.type = T, S.now = Date.now, S.isNumeric = function (e) {
            var t = S.type(e);
            return ("number" === t || "string" === t) && !isNaN(e - parseFloat(e))
        }, void 0 === (i = function () {
            return S
        }.apply(t, [])) || (e.exports = i);
        var Bt = n.jQuery, Ut = n.$;
        return S.noConflict = function (e) {
            return n.$ === S && (n.$ = Ut), e && n.jQuery === S && (n.jQuery = Bt), S
        }, o || (n.jQuery = n.$ = S), S
    })
}, function (e, t, n) {
    (function (t) {
        /**!
         * @fileOverview Kickass library to create and place poppers near their reference elements.
         * @version 1.14.3
         * @license
         * Copyright (c) 2016 Federico Zivolo and contributors
         *
         * Permission is hereby granted, free of charge, to any person obtaining a copy
         * of this software and associated documentation files (the "Software"), to deal
         * in the Software without restriction, including without limitation the rights
         * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
         * copies of the Software, and to permit persons to whom the Software is
         * furnished to do so, subject to the following conditions:
         *
         * The above copyright notice and this permission notice shall be included in all
         * copies or substantial portions of the Software.
         *
         * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
         * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
         * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
         * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
         * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
         * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
         * SOFTWARE.
         */
        !function (t, n) {
            e.exports = n()
        }(0, function () {
            "use strict";
            for (var e = "undefined" != typeof window && "undefined" != typeof document, n = ["Edge", "Trident", "Firefox"], i = 0, o = 0; o < n.length; o += 1) if (e && navigator.userAgent.indexOf(n[o]) >= 0) {
                i = 1;
                break
            }
            var r = e && window.Promise ? function (e) {
                var t = !1;
                return function () {
                    t || (t = !0, window.Promise.resolve().then(function () {
                        t = !1, e()
                    }))
                }
            } : function (e) {
                var t = !1;
                return function () {
                    t || (t = !0, setTimeout(function () {
                        t = !1, e()
                    }, i))
                }
            };

            function s(e) {
                return e && "[object Function]" === {}.toString.call(e)
            }

            function a(e, t) {
                if (1 !== e.nodeType) return [];
                var n = getComputedStyle(e, null);
                return t ? n[t] : n
            }

            function l(e) {
                return "HTML" === e.nodeName ? e : e.parentNode || e.host
            }

            function c(e) {
                if (!e) return document.body;
                switch (e.nodeName) {
                    case"HTML":
                    case"BODY":
                        return e.ownerDocument.body;
                    case"#document":
                        return e.body
                }
                var t = a(e), n = t.overflow, i = t.overflowX, o = t.overflowY;
                return /(auto|scroll|overlay)/.test(n + o + i) ? e : c(l(e))
            }

            var u = e && !(!window.MSInputMethodContext || !document.documentMode),
                d = e && /MSIE 10/.test(navigator.userAgent);

            function p(e) {
                return 11 === e ? u : 10 === e ? d : u || d
            }

            function f(e) {
                if (!e) return document.documentElement;
                for (var t = p(10) ? document.body : null, n = e.offsetParent; n === t && e.nextElementSibling;) n = (e = e.nextElementSibling).offsetParent;
                var i = n && n.nodeName;
                return i && "BODY" !== i && "HTML" !== i ? -1 !== ["TD", "TABLE"].indexOf(n.nodeName) && "static" === a(n, "position") ? f(n) : n : e ? e.ownerDocument.documentElement : document.documentElement
            }

            function h(e) {
                return null !== e.parentNode ? h(e.parentNode) : e
            }

            function m(e, t) {
                if (!(e && e.nodeType && t && t.nodeType)) return document.documentElement;
                var n = e.compareDocumentPosition(t) & Node.DOCUMENT_POSITION_FOLLOWING, i = n ? e : t, o = n ? t : e,
                    r = document.createRange();
                r.setStart(i, 0), r.setEnd(o, 0);
                var s = r.commonAncestorContainer;
                if (e !== s && t !== s || i.contains(o)) return function (e) {
                    var t = e.nodeName;
                    return "BODY" !== t && ("HTML" === t || f(e.firstElementChild) === e)
                }(s) ? s : f(s);
                var a = h(e);
                return a.host ? m(a.host, t) : m(e, h(t).host)
            }

            function g(e) {
                var t = "top" === (arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : "top") ? "scrollTop" : "scrollLeft",
                    n = e.nodeName;
                if ("BODY" === n || "HTML" === n) {
                    var i = e.ownerDocument.documentElement;
                    return (e.ownerDocument.scrollingElement || i)[t]
                }
                return e[t]
            }

            function v(e, t) {
                var n = "x" === t ? "Left" : "Top", i = "Left" === n ? "Right" : "Bottom";
                return parseFloat(e["border" + n + "Width"], 10) + parseFloat(e["border" + i + "Width"], 10)
            }

            function y(e, t, n, i) {
                return Math.max(t["offset" + e], t["scroll" + e], n["client" + e], n["offset" + e], n["scroll" + e], p(10) ? n["offset" + e] + i["margin" + ("Height" === e ? "Top" : "Left")] + i["margin" + ("Height" === e ? "Bottom" : "Right")] : 0)
            }

            function b() {
                var e = document.body, t = document.documentElement, n = p(10) && getComputedStyle(t);
                return {height: y("Height", e, t, n), width: y("Width", e, t, n)}
            }

            var w = function (e, t) {
                if (!(e instanceof t)) throw new TypeError("Cannot call a class as a function")
            }, x = function () {
                function e(e, t) {
                    for (var n = 0; n < t.length; n++) {
                        var i = t[n];
                        i.enumerable = i.enumerable || !1, i.configurable = !0, "value" in i && (i.writable = !0), Object.defineProperty(e, i.key, i)
                    }
                }

                return function (t, n, i) {
                    return n && e(t.prototype, n), i && e(t, i), t
                }
            }(), T = function (e, t, n) {
                return t in e ? Object.defineProperty(e, t, {
                    value: n,
                    enumerable: !0,
                    configurable: !0,
                    writable: !0
                }) : e[t] = n, e
            }, S = Object.assign || function (e) {
                for (var t = 1; t < arguments.length; t++) {
                    var n = arguments[t];
                    for (var i in n) Object.prototype.hasOwnProperty.call(n, i) && (e[i] = n[i])
                }
                return e
            };

            function C(e) {
                return S({}, e, {right: e.left + e.width, bottom: e.top + e.height})
            }

            function E(e) {
                var t = {};
                try {
                    if (p(10)) {
                        t = e.getBoundingClientRect();
                        var n = g(e, "top"), i = g(e, "left");
                        t.top += n, t.left += i, t.bottom += n, t.right += i
                    } else t = e.getBoundingClientRect()
                } catch (e) {
                }
                var o = {left: t.left, top: t.top, width: t.right - t.left, height: t.bottom - t.top},
                    r = "HTML" === e.nodeName ? b() : {}, s = r.width || e.clientWidth || o.right - o.left,
                    l = r.height || e.clientHeight || o.bottom - o.top, c = e.offsetWidth - s, u = e.offsetHeight - l;
                if (c || u) {
                    var d = a(e);
                    c -= v(d, "x"), u -= v(d, "y"), o.width -= c, o.height -= u
                }
                return C(o)
            }

            function k(e, t) {
                var n = arguments.length > 2 && void 0 !== arguments[2] && arguments[2], i = p(10),
                    o = "HTML" === t.nodeName, r = E(e), s = E(t), l = c(e), u = a(t),
                    d = parseFloat(u.borderTopWidth, 10), f = parseFloat(u.borderLeftWidth, 10);
                n && "HTML" === t.nodeName && (s.top = Math.max(s.top, 0), s.left = Math.max(s.left, 0));
                var h = C({top: r.top - s.top - d, left: r.left - s.left - f, width: r.width, height: r.height});
                if (h.marginTop = 0, h.marginLeft = 0, !i && o) {
                    var m = parseFloat(u.marginTop, 10), v = parseFloat(u.marginLeft, 10);
                    h.top -= d - m, h.bottom -= d - m, h.left -= f - v, h.right -= f - v, h.marginTop = m, h.marginLeft = v
                }
                return (i && !n ? t.contains(l) : t === l && "BODY" !== l.nodeName) && (h = function (e, t) {
                    var n = arguments.length > 2 && void 0 !== arguments[2] && arguments[2], i = g(t, "top"),
                        o = g(t, "left"), r = n ? -1 : 1;
                    return e.top += i * r, e.bottom += i * r, e.left += o * r, e.right += o * r, e
                }(h, t)), h
            }

            function _(e) {
                if (!e || !e.parentElement || p()) return document.documentElement;
                for (var t = e.parentElement; t && "none" === a(t, "transform");) t = t.parentElement;
                return t || document.documentElement
            }

            function A(e, t, n, i) {
                var o = arguments.length > 4 && void 0 !== arguments[4] && arguments[4], r = {top: 0, left: 0},
                    s = o ? _(e) : m(e, t);
                if ("viewport" === i) r = function (e) {
                    var t = arguments.length > 1 && void 0 !== arguments[1] && arguments[1],
                        n = e.ownerDocument.documentElement, i = k(e, n),
                        o = Math.max(n.clientWidth, window.innerWidth || 0),
                        r = Math.max(n.clientHeight, window.innerHeight || 0), s = t ? 0 : g(n),
                        a = t ? 0 : g(n, "left");
                    return C({top: s - i.top + i.marginTop, left: a - i.left + i.marginLeft, width: o, height: r})
                }(s, o); else {
                    var u = void 0;
                    "scrollParent" === i ? "BODY" === (u = c(l(t))).nodeName && (u = e.ownerDocument.documentElement) : u = "window" === i ? e.ownerDocument.documentElement : i;
                    var d = k(u, s, o);
                    if ("HTML" !== u.nodeName || function e(t) {
                        var n = t.nodeName;
                        return "BODY" !== n && "HTML" !== n && ("fixed" === a(t, "position") || e(l(t)))
                    }(s)) r = d; else {
                        var p = b(), f = p.height, h = p.width;
                        r.top += d.top - d.marginTop, r.bottom = f + d.top, r.left += d.left - d.marginLeft, r.right = h + d.left
                    }
                }
                return r.left += n, r.top += n, r.right -= n, r.bottom -= n, r
            }

            function I(e, t, n, i, o) {
                var r = arguments.length > 5 && void 0 !== arguments[5] ? arguments[5] : 0;
                if (-1 === e.indexOf("auto")) return e;
                var s = A(n, i, r, o), a = {
                    top: {width: s.width, height: t.top - s.top},
                    right: {width: s.right - t.right, height: s.height},
                    bottom: {width: s.width, height: s.bottom - t.bottom},
                    left: {width: t.left - s.left, height: s.height}
                }, l = Object.keys(a).map(function (e) {
                    return S({key: e}, a[e], {
                        area: function (e) {
                            return e.width * e.height
                        }(a[e])
                    })
                }).sort(function (e, t) {
                    return t.area - e.area
                }), c = l.filter(function (e) {
                    var t = e.width, i = e.height;
                    return t >= n.clientWidth && i >= n.clientHeight
                }), u = c.length > 0 ? c[0].key : l[0].key, d = e.split("-")[1];
                return u + (d ? "-" + d : "")
            }

            function O(e, t, n) {
                var i = arguments.length > 3 && void 0 !== arguments[3] ? arguments[3] : null;
                return k(n, i ? _(t) : m(t, n), i)
            }

            function D(e) {
                var t = getComputedStyle(e), n = parseFloat(t.marginTop) + parseFloat(t.marginBottom),
                    i = parseFloat(t.marginLeft) + parseFloat(t.marginRight);
                return {width: e.offsetWidth + i, height: e.offsetHeight + n}
            }

            function L(e) {
                var t = {left: "right", right: "left", bottom: "top", top: "bottom"};
                return e.replace(/left|right|bottom|top/g, function (e) {
                    return t[e]
                })
            }

            function N(e, t, n) {
                n = n.split("-")[0];
                var i = D(e), o = {width: i.width, height: i.height}, r = -1 !== ["right", "left"].indexOf(n),
                    s = r ? "top" : "left", a = r ? "left" : "top", l = r ? "height" : "width",
                    c = r ? "width" : "height";
                return o[s] = t[s] + t[l] / 2 - i[l] / 2, o[a] = n === a ? t[a] - i[c] : t[L(a)], o
            }

            function P(e, t) {
                return Array.prototype.find ? e.find(t) : e.filter(t)[0]
            }

            function M(e, t, n) {
                return (void 0 === n ? e : e.slice(0, function (e, t, n) {
                    if (Array.prototype.findIndex) return e.findIndex(function (e) {
                        return e[t] === n
                    });
                    var i = P(e, function (e) {
                        return e[t] === n
                    });
                    return e.indexOf(i)
                }(e, "name", n))).forEach(function (e) {
                    e.function && console.warn("`modifier.function` is deprecated, use `modifier.fn`!");
                    var n = e.function || e.fn;
                    e.enabled && s(n) && (t.offsets.popper = C(t.offsets.popper), t.offsets.reference = C(t.offsets.reference), t = n(t, e))
                }), t
            }

            function j(e, t) {
                return e.some(function (e) {
                    var n = e.name;
                    return e.enabled && n === t
                })
            }

            function H(e) {
                for (var t = [!1, "ms", "Webkit", "Moz", "O"], n = e.charAt(0).toUpperCase() + e.slice(1), i = 0; i < t.length; i++) {
                    var o = t[i], r = o ? "" + o + n : e;
                    if (void 0 !== document.body.style[r]) return r
                }
                return null
            }

            function $(e) {
                var t = e.ownerDocument;
                return t ? t.defaultView : window
            }

            function F(e, t, n, i) {
                n.updateBound = i, $(e).addEventListener("resize", n.updateBound, {passive: !0});
                var o = c(e);
                return function e(t, n, i, o) {
                    var r = "BODY" === t.nodeName, s = r ? t.ownerDocument.defaultView : t;
                    s.addEventListener(n, i, {passive: !0}), r || e(c(s.parentNode), n, i, o), o.push(s)
                }(o, "scroll", n.updateBound, n.scrollParents), n.scrollElement = o, n.eventsEnabled = !0, n
            }

            function R() {
                this.state.eventsEnabled && (cancelAnimationFrame(this.scheduleUpdate), this.state = function (e, t) {
                    return $(e).removeEventListener("resize", t.updateBound), t.scrollParents.forEach(function (e) {
                        e.removeEventListener("scroll", t.updateBound)
                    }), t.updateBound = null, t.scrollParents = [], t.scrollElement = null, t.eventsEnabled = !1, t
                }(this.reference, this.state))
            }

            function W(e) {
                return "" !== e && !isNaN(parseFloat(e)) && isFinite(e)
            }

            function V(e, t) {
                Object.keys(t).forEach(function (n) {
                    var i = "";
                    -1 !== ["width", "height", "top", "right", "bottom", "left"].indexOf(n) && W(t[n]) && (i = "px"), e.style[n] = t[n] + i
                })
            }

            function z(e, t, n) {
                var i = P(e, function (e) {
                    return e.name === t
                }), o = !!i && e.some(function (e) {
                    return e.name === n && e.enabled && e.order < i.order
                });
                if (!o) {
                    var r = "`" + t + "`", s = "`" + n + "`";
                    console.warn(s + " modifier is required by " + r + " modifier in order to work, be sure to include it before " + r + "!")
                }
                return o
            }

            var q = ["auto-start", "auto", "auto-end", "top-start", "top", "top-end", "right-start", "right", "right-end", "bottom-end", "bottom", "bottom-start", "left-end", "left", "left-start"],
                B = q.slice(3);

            function U(e) {
                var t = arguments.length > 1 && void 0 !== arguments[1] && arguments[1], n = B.indexOf(e),
                    i = B.slice(n + 1).concat(B.slice(0, n));
                return t ? i.reverse() : i
            }

            var G = {FLIP: "flip", CLOCKWISE: "clockwise", COUNTERCLOCKWISE: "counterclockwise"};

            function Y(e, t, n, i) {
                var o = [0, 0], r = -1 !== ["right", "left"].indexOf(i), s = e.split(/(\+|\-)/).map(function (e) {
                    return e.trim()
                }), a = s.indexOf(P(s, function (e) {
                    return -1 !== e.search(/,|\s/)
                }));
                s[a] && -1 === s[a].indexOf(",") && console.warn("Offsets separated by white space(s) are deprecated, use a comma (,) instead.");
                var l = /\s*,\s*|\s+/,
                    c = -1 !== a ? [s.slice(0, a).concat([s[a].split(l)[0]]), [s[a].split(l)[1]].concat(s.slice(a + 1))] : [s];
                return (c = c.map(function (e, i) {
                    var o = (1 === i ? !r : r) ? "height" : "width", s = !1;
                    return e.reduce(function (e, t) {
                        return "" === e[e.length - 1] && -1 !== ["+", "-"].indexOf(t) ? (e[e.length - 1] = t, s = !0, e) : s ? (e[e.length - 1] += t, s = !1, e) : e.concat(t)
                    }, []).map(function (e) {
                        return function (e, t, n, i) {
                            var o = e.match(/((?:\-|\+)?\d*\.?\d*)(.*)/), r = +o[1], s = o[2];
                            if (!r) return e;
                            if (0 === s.indexOf("%")) {
                                var a = void 0;
                                switch (s) {
                                    case"%p":
                                        a = n;
                                        break;
                                    case"%":
                                    case"%r":
                                    default:
                                        a = i
                                }
                                return C(a)[t] / 100 * r
                            }
                            if ("vh" === s || "vw" === s) return ("vh" === s ? Math.max(document.documentElement.clientHeight, window.innerHeight || 0) : Math.max(document.documentElement.clientWidth, window.innerWidth || 0)) / 100 * r;
                            return r
                        }(e, o, t, n)
                    })
                })).forEach(function (e, t) {
                    e.forEach(function (n, i) {
                        W(n) && (o[t] += n * ("-" === e[i - 1] ? -1 : 1))
                    })
                }), o
            }

            var K = {
                placement: "bottom", positionFixed: !1, eventsEnabled: !0, removeOnDestroy: !1, onCreate: function () {
                }, onUpdate: function () {
                }, modifiers: {
                    shift: {
                        order: 100, enabled: !0, fn: function (e) {
                            var t = e.placement, n = t.split("-")[0], i = t.split("-")[1];
                            if (i) {
                                var o = e.offsets, r = o.reference, s = o.popper,
                                    a = -1 !== ["bottom", "top"].indexOf(n), l = a ? "left" : "top",
                                    c = a ? "width" : "height",
                                    u = {start: T({}, l, r[l]), end: T({}, l, r[l] + r[c] - s[c])};
                                e.offsets.popper = S({}, s, u[i])
                            }
                            return e
                        }
                    }, offset: {
                        order: 200, enabled: !0, fn: function (e, t) {
                            var n = t.offset, i = e.placement, o = e.offsets, r = o.popper, s = o.reference,
                                a = i.split("-")[0], l = void 0;
                            return l = W(+n) ? [+n, 0] : Y(n, r, s, a), "left" === a ? (r.top += l[0], r.left -= l[1]) : "right" === a ? (r.top += l[0], r.left += l[1]) : "top" === a ? (r.left += l[0], r.top -= l[1]) : "bottom" === a && (r.left += l[0], r.top += l[1]), e.popper = r, e
                        }, offset: 0
                    }, preventOverflow: {
                        order: 300, enabled: !0, fn: function (e, t) {
                            var n = t.boundariesElement || f(e.instance.popper);
                            e.instance.reference === n && (n = f(n));
                            var i = H("transform"), o = e.instance.popper.style, r = o.top, s = o.left, a = o[i];
                            o.top = "", o.left = "", o[i] = "";
                            var l = A(e.instance.popper, e.instance.reference, t.padding, n, e.positionFixed);
                            o.top = r, o.left = s, o[i] = a, t.boundaries = l;
                            var c = t.priority, u = e.offsets.popper, d = {
                                primary: function (e) {
                                    var n = u[e];
                                    return u[e] < l[e] && !t.escapeWithReference && (n = Math.max(u[e], l[e])), T({}, e, n)
                                }, secondary: function (e) {
                                    var n = "right" === e ? "left" : "top", i = u[n];
                                    return u[e] > l[e] && !t.escapeWithReference && (i = Math.min(u[n], l[e] - ("right" === e ? u.width : u.height))), T({}, n, i)
                                }
                            };
                            return c.forEach(function (e) {
                                var t = -1 !== ["left", "top"].indexOf(e) ? "primary" : "secondary";
                                u = S({}, u, d[t](e))
                            }), e.offsets.popper = u, e
                        }, priority: ["left", "right", "top", "bottom"], padding: 5, boundariesElement: "scrollParent"
                    }, keepTogether: {
                        order: 400, enabled: !0, fn: function (e) {
                            var t = e.offsets, n = t.popper, i = t.reference, o = e.placement.split("-")[0],
                                r = Math.floor, s = -1 !== ["top", "bottom"].indexOf(o), a = s ? "right" : "bottom",
                                l = s ? "left" : "top", c = s ? "width" : "height";
                            return n[a] < r(i[l]) && (e.offsets.popper[l] = r(i[l]) - n[c]), n[l] > r(i[a]) && (e.offsets.popper[l] = r(i[a])), e
                        }
                    }, arrow: {
                        order: 500, enabled: !0, fn: function (e, t) {
                            var n;
                            if (!z(e.instance.modifiers, "arrow", "keepTogether")) return e;
                            var i = t.element;
                            if ("string" == typeof i) {
                                if (!(i = e.instance.popper.querySelector(i))) return e
                            } else if (!e.instance.popper.contains(i)) return console.warn("WARNING: `arrow.element` must be child of its popper element!"), e;
                            var o = e.placement.split("-")[0], r = e.offsets, s = r.popper, l = r.reference,
                                c = -1 !== ["left", "right"].indexOf(o), u = c ? "height" : "width",
                                d = c ? "Top" : "Left", p = d.toLowerCase(), f = c ? "left" : "top",
                                h = c ? "bottom" : "right", m = D(i)[u];
                            l[h] - m < s[p] && (e.offsets.popper[p] -= s[p] - (l[h] - m)), l[p] + m > s[h] && (e.offsets.popper[p] += l[p] + m - s[h]), e.offsets.popper = C(e.offsets.popper);
                            var g = l[p] + l[u] / 2 - m / 2, v = a(e.instance.popper),
                                y = parseFloat(v["margin" + d], 10), b = parseFloat(v["border" + d + "Width"], 10),
                                w = g - e.offsets.popper[p] - y - b;
                            return w = Math.max(Math.min(s[u] - m, w), 0), e.arrowElement = i, e.offsets.arrow = (T(n = {}, p, Math.round(w)), T(n, f, ""), n), e
                        }, element: "[x-arrow]"
                    }, flip: {
                        order: 600, enabled: !0, fn: function (e, t) {
                            if (j(e.instance.modifiers, "inner")) return e;
                            if (e.flipped && e.placement === e.originalPlacement) return e;
                            var n = A(e.instance.popper, e.instance.reference, t.padding, t.boundariesElement, e.positionFixed),
                                i = e.placement.split("-")[0], o = L(i), r = e.placement.split("-")[1] || "", s = [];
                            switch (t.behavior) {
                                case G.FLIP:
                                    s = [i, o];
                                    break;
                                case G.CLOCKWISE:
                                    s = U(i);
                                    break;
                                case G.COUNTERCLOCKWISE:
                                    s = U(i, !0);
                                    break;
                                default:
                                    s = t.behavior
                            }
                            return s.forEach(function (a, l) {
                                if (i !== a || s.length === l + 1) return e;
                                i = e.placement.split("-")[0], o = L(i);
                                var c = e.offsets.popper, u = e.offsets.reference, d = Math.floor,
                                    p = "left" === i && d(c.right) > d(u.left) || "right" === i && d(c.left) < d(u.right) || "top" === i && d(c.bottom) > d(u.top) || "bottom" === i && d(c.top) < d(u.bottom),
                                    f = d(c.left) < d(n.left), h = d(c.right) > d(n.right), m = d(c.top) < d(n.top),
                                    g = d(c.bottom) > d(n.bottom),
                                    v = "left" === i && f || "right" === i && h || "top" === i && m || "bottom" === i && g,
                                    y = -1 !== ["top", "bottom"].indexOf(i),
                                    b = !!t.flipVariations && (y && "start" === r && f || y && "end" === r && h || !y && "start" === r && m || !y && "end" === r && g);
                                (p || v || b) && (e.flipped = !0, (p || v) && (i = s[l + 1]), b && (r = function (e) {
                                    return "end" === e ? "start" : "start" === e ? "end" : e
                                }(r)), e.placement = i + (r ? "-" + r : ""), e.offsets.popper = S({}, e.offsets.popper, N(e.instance.popper, e.offsets.reference, e.placement)), e = M(e.instance.modifiers, e, "flip"))
                            }), e
                        }, behavior: "flip", padding: 5, boundariesElement: "viewport"
                    }, inner: {
                        order: 700, enabled: !1, fn: function (e) {
                            var t = e.placement, n = t.split("-")[0], i = e.offsets, o = i.popper, r = i.reference,
                                s = -1 !== ["left", "right"].indexOf(n), a = -1 === ["top", "left"].indexOf(n);
                            return o[s ? "left" : "top"] = r[n] - (a ? o[s ? "width" : "height"] : 0), e.placement = L(t), e.offsets.popper = C(o), e
                        }
                    }, hide: {
                        order: 800, enabled: !0, fn: function (e) {
                            if (!z(e.instance.modifiers, "hide", "preventOverflow")) return e;
                            var t = e.offsets.reference, n = P(e.instance.modifiers, function (e) {
                                return "preventOverflow" === e.name
                            }).boundaries;
                            if (t.bottom < n.top || t.left > n.right || t.top > n.bottom || t.right < n.left) {
                                if (!0 === e.hide) return e;
                                e.hide = !0, e.attributes["x-out-of-boundaries"] = ""
                            } else {
                                if (!1 === e.hide) return e;
                                e.hide = !1, e.attributes["x-out-of-boundaries"] = !1
                            }
                            return e
                        }
                    }, computeStyle: {
                        order: 850, enabled: !0, fn: function (e, t) {
                            var n = t.x, i = t.y, o = e.offsets.popper, r = P(e.instance.modifiers, function (e) {
                                return "applyStyle" === e.name
                            }).gpuAcceleration;
                            void 0 !== r && console.warn("WARNING: `gpuAcceleration` option moved to `computeStyle` modifier and will not be supported in future versions of Popper.js!");
                            var s = void 0 !== r ? r : t.gpuAcceleration, a = E(f(e.instance.popper)),
                                l = {position: o.position}, c = {
                                    left: Math.floor(o.left),
                                    top: Math.round(o.top),
                                    bottom: Math.round(o.bottom),
                                    right: Math.floor(o.right)
                                }, u = "bottom" === n ? "top" : "bottom", d = "right" === i ? "left" : "right",
                                p = H("transform"), h = void 0, m = void 0;
                            if (m = "bottom" === u ? -a.height + c.bottom : c.top, h = "right" === d ? -a.width + c.right : c.left, s && p) l[p] = "translate3d(" + h + "px, " + m + "px, 0)", l[u] = 0, l[d] = 0, l.willChange = "transform"; else {
                                var g = "bottom" === u ? -1 : 1, v = "right" === d ? -1 : 1;
                                l[u] = m * g, l[d] = h * v, l.willChange = u + ", " + d
                            }
                            var y = {"x-placement": e.placement};
                            return e.attributes = S({}, y, e.attributes), e.styles = S({}, l, e.styles), e.arrowStyles = S({}, e.offsets.arrow, e.arrowStyles), e
                        }, gpuAcceleration: !0, x: "bottom", y: "right"
                    }, applyStyle: {
                        order: 900, enabled: !0, fn: function (e) {
                            return V(e.instance.popper, e.styles), function (e, t) {
                                Object.keys(t).forEach(function (n) {
                                    !1 !== t[n] ? e.setAttribute(n, t[n]) : e.removeAttribute(n)
                                })
                            }(e.instance.popper, e.attributes), e.arrowElement && Object.keys(e.arrowStyles).length && V(e.arrowElement, e.arrowStyles), e
                        }, onLoad: function (e, t, n, i, o) {
                            var r = O(o, t, e, n.positionFixed),
                                s = I(n.placement, r, t, e, n.modifiers.flip.boundariesElement, n.modifiers.flip.padding);
                            return t.setAttribute("x-placement", s), V(t, {position: n.positionFixed ? "fixed" : "absolute"}), n
                        }, gpuAcceleration: void 0
                    }
                }
            }, Q = function () {
                function e(t, n) {
                    var i = this, o = arguments.length > 2 && void 0 !== arguments[2] ? arguments[2] : {};
                    w(this, e), this.scheduleUpdate = function () {
                        return requestAnimationFrame(i.update)
                    }, this.update = r(this.update.bind(this)), this.options = S({}, e.Defaults, o), this.state = {
                        isDestroyed: !1,
                        isCreated: !1,
                        scrollParents: []
                    }, this.reference = t && t.jquery ? t[0] : t, this.popper = n && n.jquery ? n[0] : n, this.options.modifiers = {}, Object.keys(S({}, e.Defaults.modifiers, o.modifiers)).forEach(function (t) {
                        i.options.modifiers[t] = S({}, e.Defaults.modifiers[t] || {}, o.modifiers ? o.modifiers[t] : {})
                    }), this.modifiers = Object.keys(this.options.modifiers).map(function (e) {
                        return S({name: e}, i.options.modifiers[e])
                    }).sort(function (e, t) {
                        return e.order - t.order
                    }), this.modifiers.forEach(function (e) {
                        e.enabled && s(e.onLoad) && e.onLoad(i.reference, i.popper, i.options, e, i.state)
                    }), this.update();
                    var a = this.options.eventsEnabled;
                    a && this.enableEventListeners(), this.state.eventsEnabled = a
                }

                return x(e, [{
                    key: "update", value: function () {
                        return function () {
                            if (!this.state.isDestroyed) {
                                var e = {
                                    instance: this,
                                    styles: {},
                                    arrowStyles: {},
                                    attributes: {},
                                    flipped: !1,
                                    offsets: {}
                                };
                                e.offsets.reference = O(this.state, this.popper, this.reference, this.options.positionFixed), e.placement = I(this.options.placement, e.offsets.reference, this.popper, this.reference, this.options.modifiers.flip.boundariesElement, this.options.modifiers.flip.padding), e.originalPlacement = e.placement, e.positionFixed = this.options.positionFixed, e.offsets.popper = N(this.popper, e.offsets.reference, e.placement), e.offsets.popper.position = this.options.positionFixed ? "fixed" : "absolute", e = M(this.modifiers, e), this.state.isCreated ? this.options.onUpdate(e) : (this.state.isCreated = !0, this.options.onCreate(e))
                            }
                        }.call(this)
                    }
                }, {
                    key: "destroy", value: function () {
                        return function () {
                            return this.state.isDestroyed = !0, j(this.modifiers, "applyStyle") && (this.popper.removeAttribute("x-placement"), this.popper.style.position = "", this.popper.style.top = "", this.popper.style.left = "", this.popper.style.right = "", this.popper.style.bottom = "", this.popper.style.willChange = "", this.popper.style[H("transform")] = ""), this.disableEventListeners(), this.options.removeOnDestroy && this.popper.parentNode.removeChild(this.popper), this
                        }.call(this)
                    }
                }, {
                    key: "enableEventListeners", value: function () {
                        return function () {
                            this.state.eventsEnabled || (this.state = F(this.reference, this.options, this.state, this.scheduleUpdate))
                        }.call(this)
                    }
                }, {
                    key: "disableEventListeners", value: function () {
                        return R.call(this)
                    }
                }]), e
            }();
            return Q.Utils = ("undefined" != typeof window ? window : t).PopperUtils, Q.placements = q, Q.Defaults = K, Q
        })
    }).call(this, n(5))
}, function (e, t) {
    var n;
    n = function () {
        return this
    }();
    try {
        n = n || Function("return this")() || (0, eval)("this")
    } catch (e) {
        "object" == typeof window && (n = window)
    }
    e.exports = n
}, function (e, t, n) {
    /*!
  * Bootstrap v4.1.2 (https://getbootstrap.com/)
  * Copyright 2011-2018 The Bootstrap Authors (https://github.com/twbs/bootstrap/graphs/contributors)
  * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
  */
    !function (e, t, n) {
        "use strict";

        function i(e, t) {
            for (var n = 0; n < t.length; n++) {
                var i = t[n];
                i.enumerable = i.enumerable || !1, i.configurable = !0, "value" in i && (i.writable = !0), Object.defineProperty(e, i.key, i)
            }
        }

        function o(e, t, n) {
            return t && i(e.prototype, t), n && i(e, n), e
        }

        function r(e, t, n) {
            return t in e ? Object.defineProperty(e, t, {
                value: n,
                enumerable: !0,
                configurable: !0,
                writable: !0
            }) : e[t] = n, e
        }

        function s(e) {
            for (var t = 1; t < arguments.length; t++) {
                var n = null != arguments[t] ? arguments[t] : {}, i = Object.keys(n);
                "function" == typeof Object.getOwnPropertySymbols && (i = i.concat(Object.getOwnPropertySymbols(n).filter(function (e) {
                    return Object.getOwnPropertyDescriptor(n, e).enumerable
                }))), i.forEach(function (t) {
                    r(e, t, n[t])
                })
            }
            return e
        }

        t = t && t.hasOwnProperty("default") ? t.default : t, n = n && n.hasOwnProperty("default") ? n.default : n;
        var a = function (e) {
            var t = "transitionend";

            function n(e) {
                return {}.toString.call(e).match(/\s([a-z]+)/i)[1].toLowerCase()
            }

            function i(t) {
                var n = this, i = !1;
                return e(this).one(o.TRANSITION_END, function () {
                    i = !0
                }), setTimeout(function () {
                    i || o.triggerTransitionEnd(n)
                }, t), this
            }

            var o = {
                TRANSITION_END: "bsTransitionEnd", getUID: function (e) {
                    do {
                        e += ~~(1e6 * Math.random())
                    } while (document.getElementById(e));
                    return e
                }, getSelectorFromElement: function (e) {
                    var t = e.getAttribute("data-target");
                    t && "#" !== t || (t = e.getAttribute("href") || "");
                    try {
                        return document.querySelector(t) ? t : null
                    } catch (e) {
                        return null
                    }
                }, getTransitionDurationFromElement: function (t) {
                    if (!t) return 0;
                    var n = e(t).css("transition-duration"), i = parseFloat(n);
                    return i ? (n = n.split(",")[0], 1e3 * parseFloat(n)) : 0
                }, reflow: function (e) {
                    return e.offsetHeight
                }, triggerTransitionEnd: function (n) {
                    e(n).trigger(t)
                }, supportsTransitionEnd: function () {
                    return Boolean(t)
                }, isElement: function (e) {
                    return (e[0] || e).nodeType
                }, typeCheckConfig: function (e, t, i) {
                    for (var r in i) if (Object.prototype.hasOwnProperty.call(i, r)) {
                        var s = i[r], a = t[r], l = a && o.isElement(a) ? "element" : n(a);
                        if (!new RegExp(s).test(l)) throw new Error(e.toUpperCase() + ': Option "' + r + '" provided type "' + l + '" but expected type "' + s + '".')
                    }
                }
            };
            return e.fn.emulateTransitionEnd = i, e.event.special[o.TRANSITION_END] = {
                bindType: t,
                delegateType: t,
                handle: function (t) {
                    if (e(t.target).is(this)) return t.handleObj.handler.apply(this, arguments)
                }
            }, o
        }(t), l = function (e) {
            var t = e.fn.alert,
                n = {CLOSE: "close.bs.alert", CLOSED: "closed.bs.alert", CLICK_DATA_API: "click.bs.alert.data-api"},
                i = {ALERT: "alert", FADE: "fade", SHOW: "show"}, r = function () {
                    function t(e) {
                        this._element = e
                    }

                    var r = t.prototype;
                    return r.close = function (e) {
                        var t = this._element;
                        e && (t = this._getRootElement(e));
                        var n = this._triggerCloseEvent(t);
                        n.isDefaultPrevented() || this._removeElement(t)
                    }, r.dispose = function () {
                        e.removeData(this._element, "bs.alert"), this._element = null
                    }, r._getRootElement = function (t) {
                        var n = a.getSelectorFromElement(t), o = !1;
                        return n && (o = document.querySelector(n)), o || (o = e(t).closest("." + i.ALERT)[0]), o
                    }, r._triggerCloseEvent = function (t) {
                        var i = e.Event(n.CLOSE);
                        return e(t).trigger(i), i
                    }, r._removeElement = function (t) {
                        var n = this;
                        if (e(t).removeClass(i.SHOW), e(t).hasClass(i.FADE)) {
                            var o = a.getTransitionDurationFromElement(t);
                            e(t).one(a.TRANSITION_END, function (e) {
                                return n._destroyElement(t, e)
                            }).emulateTransitionEnd(o)
                        } else this._destroyElement(t)
                    }, r._destroyElement = function (t) {
                        e(t).detach().trigger(n.CLOSED).remove()
                    }, t._jQueryInterface = function (n) {
                        return this.each(function () {
                            var i = e(this), o = i.data("bs.alert");
                            o || (o = new t(this), i.data("bs.alert", o)), "close" === n && o[n](this)
                        })
                    }, t._handleDismiss = function (e) {
                        return function (t) {
                            t && t.preventDefault(), e.close(this)
                        }
                    }, o(t, null, [{
                        key: "VERSION", get: function () {
                            return "4.1.2"
                        }
                    }]), t
                }();
            return e(document).on(n.CLICK_DATA_API, '[data-dismiss="alert"]', r._handleDismiss(new r)), e.fn.alert = r._jQueryInterface, e.fn.alert.Constructor = r, e.fn.alert.noConflict = function () {
                return e.fn.alert = t, r._jQueryInterface
            }, r
        }(t), c = function (e) {
            var t = "button", n = e.fn[t], i = {ACTIVE: "active", BUTTON: "btn", FOCUS: "focus"}, r = {
                DATA_TOGGLE_CARROT: '[data-toggle^="button"]',
                DATA_TOGGLE: '[data-toggle="buttons"]',
                INPUT: "input",
                ACTIVE: ".active",
                BUTTON: ".btn"
            }, s = {
                CLICK_DATA_API: "click.bs.button.data-api",
                FOCUS_BLUR_DATA_API: "focus.bs.button.data-api blur.bs.button.data-api"
            }, a = function () {
                function t(e) {
                    this._element = e
                }

                var n = t.prototype;
                return n.toggle = function () {
                    var t = !0, n = !0, o = e(this._element).closest(r.DATA_TOGGLE)[0];
                    if (o) {
                        var s = this._element.querySelector(r.INPUT);
                        if (s) {
                            if ("radio" === s.type) if (s.checked && this._element.classList.contains(i.ACTIVE)) t = !1; else {
                                var a = o.querySelector(r.ACTIVE);
                                a && e(a).removeClass(i.ACTIVE)
                            }
                            if (t) {
                                if (s.hasAttribute("disabled") || o.hasAttribute("disabled") || s.classList.contains("disabled") || o.classList.contains("disabled")) return;
                                s.checked = !this._element.classList.contains(i.ACTIVE), e(s).trigger("change")
                            }
                            s.focus(), n = !1
                        }
                    }
                    n && this._element.setAttribute("aria-pressed", !this._element.classList.contains(i.ACTIVE)), t && e(this._element).toggleClass(i.ACTIVE)
                }, n.dispose = function () {
                    e.removeData(this._element, "bs.button"), this._element = null
                }, t._jQueryInterface = function (n) {
                    return this.each(function () {
                        var i = e(this).data("bs.button");
                        i || (i = new t(this), e(this).data("bs.button", i)), "toggle" === n && i[n]()
                    })
                }, o(t, null, [{
                    key: "VERSION", get: function () {
                        return "4.1.2"
                    }
                }]), t
            }();
            return e(document).on(s.CLICK_DATA_API, r.DATA_TOGGLE_CARROT, function (t) {
                t.preventDefault();
                var n = t.target;
                e(n).hasClass(i.BUTTON) || (n = e(n).closest(r.BUTTON)), a._jQueryInterface.call(e(n), "toggle")
            }).on(s.FOCUS_BLUR_DATA_API, r.DATA_TOGGLE_CARROT, function (t) {
                var n = e(t.target).closest(r.BUTTON)[0];
                e(n).toggleClass(i.FOCUS, /^focus(in)?$/.test(t.type))
            }), e.fn[t] = a._jQueryInterface, e.fn[t].Constructor = a, e.fn[t].noConflict = function () {
                return e.fn[t] = n, a._jQueryInterface
            }, a
        }(t), u = function (e) {
            var t = "carousel", n = "bs.carousel", i = "." + n, r = e.fn[t],
                l = {interval: 5e3, keyboard: !0, slide: !1, pause: "hover", wrap: !0}, c = {
                    interval: "(number|boolean)",
                    keyboard: "boolean",
                    slide: "(boolean|string)",
                    pause: "(string|boolean)",
                    wrap: "boolean"
                }, u = {NEXT: "next", PREV: "prev", LEFT: "left", RIGHT: "right"}, d = {
                    SLIDE: "slide" + i,
                    SLID: "slid" + i,
                    KEYDOWN: "keydown" + i,
                    MOUSEENTER: "mouseenter" + i,
                    MOUSELEAVE: "mouseleave" + i,
                    TOUCHEND: "touchend" + i,
                    LOAD_DATA_API: "load.bs.carousel.data-api",
                    CLICK_DATA_API: "click.bs.carousel.data-api"
                }, p = {
                    CAROUSEL: "carousel",
                    ACTIVE: "active",
                    SLIDE: "slide",
                    RIGHT: "carousel-item-right",
                    LEFT: "carousel-item-left",
                    NEXT: "carousel-item-next",
                    PREV: "carousel-item-prev",
                    ITEM: "carousel-item"
                }, f = {
                    ACTIVE: ".active",
                    ACTIVE_ITEM: ".active.carousel-item",
                    ITEM: ".carousel-item",
                    NEXT_PREV: ".carousel-item-next, .carousel-item-prev",
                    INDICATORS: ".carousel-indicators",
                    DATA_SLIDE: "[data-slide], [data-slide-to]",
                    DATA_RIDE: '[data-ride="carousel"]'
                }, h = function () {
                    function r(t, n) {
                        this._items = null, this._interval = null, this._activeElement = null, this._isPaused = !1, this._isSliding = !1, this.touchTimeout = null, this._config = this._getConfig(n), this._element = e(t)[0], this._indicatorsElement = this._element.querySelector(f.INDICATORS), this._addEventListeners()
                    }

                    var h = r.prototype;
                    return h.next = function () {
                        this._isSliding || this._slide(u.NEXT)
                    }, h.nextWhenVisible = function () {
                        !document.hidden && e(this._element).is(":visible") && "hidden" !== e(this._element).css("visibility") && this.next()
                    }, h.prev = function () {
                        this._isSliding || this._slide(u.PREV)
                    }, h.pause = function (e) {
                        e || (this._isPaused = !0), this._element.querySelector(f.NEXT_PREV) && (a.triggerTransitionEnd(this._element), this.cycle(!0)), clearInterval(this._interval), this._interval = null
                    }, h.cycle = function (e) {
                        e || (this._isPaused = !1), this._interval && (clearInterval(this._interval), this._interval = null), this._config.interval && !this._isPaused && (this._interval = setInterval((document.visibilityState ? this.nextWhenVisible : this.next).bind(this), this._config.interval))
                    }, h.to = function (t) {
                        var n = this;
                        this._activeElement = this._element.querySelector(f.ACTIVE_ITEM);
                        var i = this._getItemIndex(this._activeElement);
                        if (!(t > this._items.length - 1 || t < 0)) if (this._isSliding) e(this._element).one(d.SLID, function () {
                            return n.to(t)
                        }); else {
                            if (i === t) return this.pause(), void this.cycle();
                            var o = t > i ? u.NEXT : u.PREV;
                            this._slide(o, this._items[t])
                        }
                    }, h.dispose = function () {
                        e(this._element).off(i), e.removeData(this._element, n), this._items = null, this._config = null, this._element = null, this._interval = null, this._isPaused = null, this._isSliding = null, this._activeElement = null, this._indicatorsElement = null
                    }, h._getConfig = function (e) {
                        return e = s({}, l, e), a.typeCheckConfig(t, e, c), e
                    }, h._addEventListeners = function () {
                        var t = this;
                        this._config.keyboard && e(this._element).on(d.KEYDOWN, function (e) {
                            return t._keydown(e)
                        }), "hover" === this._config.pause && (e(this._element).on(d.MOUSEENTER, function (e) {
                            return t.pause(e)
                        }).on(d.MOUSELEAVE, function (e) {
                            return t.cycle(e)
                        }), "ontouchstart" in document.documentElement && e(this._element).on(d.TOUCHEND, function () {
                            t.pause(), t.touchTimeout && clearTimeout(t.touchTimeout), t.touchTimeout = setTimeout(function (e) {
                                return t.cycle(e)
                            }, 500 + t._config.interval)
                        }))
                    }, h._keydown = function (e) {
                        if (!/input|textarea/i.test(e.target.tagName)) switch (e.which) {
                            case 37:
                                e.preventDefault(), this.prev();
                                break;
                            case 39:
                                e.preventDefault(), this.next()
                        }
                    }, h._getItemIndex = function (e) {
                        return this._items = e && e.parentNode ? [].slice.call(e.parentNode.querySelectorAll(f.ITEM)) : [], this._items.indexOf(e)
                    }, h._getItemByDirection = function (e, t) {
                        var n = e === u.NEXT, i = e === u.PREV, o = this._getItemIndex(t), r = this._items.length - 1,
                            s = i && 0 === o || n && o === r;
                        if (s && !this._config.wrap) return t;
                        var a = e === u.PREV ? -1 : 1, l = (o + a) % this._items.length;
                        return -1 === l ? this._items[this._items.length - 1] : this._items[l]
                    }, h._triggerSlideEvent = function (t, n) {
                        var i = this._getItemIndex(t), o = this._getItemIndex(this._element.querySelector(f.ACTIVE_ITEM)),
                            r = e.Event(d.SLIDE, {relatedTarget: t, direction: n, from: o, to: i});
                        return e(this._element).trigger(r), r
                    }, h._setActiveIndicatorElement = function (t) {
                        if (this._indicatorsElement) {
                            var n = [].slice.call(this._indicatorsElement.querySelectorAll(f.ACTIVE));
                            e(n).removeClass(p.ACTIVE);
                            var i = this._indicatorsElement.children[this._getItemIndex(t)];
                            i && e(i).addClass(p.ACTIVE)
                        }
                    }, h._slide = function (t, n) {
                        var i, o, r, s = this, l = this._element.querySelector(f.ACTIVE_ITEM), c = this._getItemIndex(l),
                            h = n || l && this._getItemByDirection(t, l), m = this._getItemIndex(h),
                            g = Boolean(this._interval);
                        if (t === u.NEXT ? (i = p.LEFT, o = p.NEXT, r = u.LEFT) : (i = p.RIGHT, o = p.PREV, r = u.RIGHT), h && e(h).hasClass(p.ACTIVE)) this._isSliding = !1; else {
                            var v = this._triggerSlideEvent(h, r);
                            if (!v.isDefaultPrevented() && l && h) {
                                this._isSliding = !0, g && this.pause(), this._setActiveIndicatorElement(h);
                                var y = e.Event(d.SLID, {relatedTarget: h, direction: r, from: c, to: m});
                                if (e(this._element).hasClass(p.SLIDE)) {
                                    e(h).addClass(o), a.reflow(h), e(l).addClass(i), e(h).addClass(i);
                                    var b = a.getTransitionDurationFromElement(l);
                                    e(l).one(a.TRANSITION_END, function () {
                                        e(h).removeClass(i + " " + o).addClass(p.ACTIVE), e(l).removeClass(p.ACTIVE + " " + o + " " + i), s._isSliding = !1, setTimeout(function () {
                                            return e(s._element).trigger(y)
                                        }, 0)
                                    }).emulateTransitionEnd(b)
                                } else e(l).removeClass(p.ACTIVE), e(h).addClass(p.ACTIVE), this._isSliding = !1, e(this._element).trigger(y);
                                g && this.cycle()
                            }
                        }
                    }, r._jQueryInterface = function (t) {
                        return this.each(function () {
                            var i = e(this).data(n), o = s({}, l, e(this).data());
                            "object" == typeof t && (o = s({}, o, t));
                            var a = "string" == typeof t ? t : o.slide;
                            if (i || (i = new r(this, o), e(this).data(n, i)), "number" == typeof t) i.to(t); else if ("string" == typeof a) {
                                if (void 0 === i[a]) throw new TypeError('No method named "' + a + '"');
                                i[a]()
                            } else o.interval && (i.pause(), i.cycle())
                        })
                    }, r._dataApiClickHandler = function (t) {
                        var i = a.getSelectorFromElement(this);
                        if (i) {
                            var o = e(i)[0];
                            if (o && e(o).hasClass(p.CAROUSEL)) {
                                var l = s({}, e(o).data(), e(this).data()), c = this.getAttribute("data-slide-to");
                                c && (l.interval = !1), r._jQueryInterface.call(e(o), l), c && e(o).data(n).to(c), t.preventDefault()
                            }
                        }
                    }, o(r, null, [{
                        key: "VERSION", get: function () {
                            return "4.1.2"
                        }
                    }, {
                        key: "Default", get: function () {
                            return l
                        }
                    }]), r
                }();
            return e(document).on(d.CLICK_DATA_API, f.DATA_SLIDE, h._dataApiClickHandler), e(window).on(d.LOAD_DATA_API, function () {
                for (var t = [].slice.call(document.querySelectorAll(f.DATA_RIDE)), n = 0, i = t.length; n < i; n++) {
                    var o = e(t[n]);
                    h._jQueryInterface.call(o, o.data())
                }
            }), e.fn[t] = h._jQueryInterface, e.fn[t].Constructor = h, e.fn[t].noConflict = function () {
                return e.fn[t] = r, h._jQueryInterface
            }, h
        }(t), d = function (e) {
            var t = "collapse", n = "bs.collapse", i = e.fn[t], r = {toggle: !0, parent: ""},
                l = {toggle: "boolean", parent: "(string|element)"}, c = {
                    SHOW: "show.bs.collapse",
                    SHOWN: "shown.bs.collapse",
                    HIDE: "hide.bs.collapse",
                    HIDDEN: "hidden.bs.collapse",
                    CLICK_DATA_API: "click.bs.collapse.data-api"
                }, u = {SHOW: "show", COLLAPSE: "collapse", COLLAPSING: "collapsing", COLLAPSED: "collapsed"},
                d = {WIDTH: "width", HEIGHT: "height"},
                p = {ACTIVES: ".show, .collapsing", DATA_TOGGLE: '[data-toggle="collapse"]'}, f = function () {
                    function i(t, n) {
                        this._isTransitioning = !1, this._element = t, this._config = this._getConfig(n), this._triggerArray = e.makeArray(document.querySelectorAll('[data-toggle="collapse"][href="#' + t.id + '"],[data-toggle="collapse"][data-target="#' + t.id + '"]'));
                        for (var i = [].slice.call(document.querySelectorAll(p.DATA_TOGGLE)), o = 0, r = i.length; o < r; o++) {
                            var s = i[o], l = a.getSelectorFromElement(s),
                                c = [].slice.call(document.querySelectorAll(l)).filter(function (e) {
                                    return e === t
                                });
                            null !== l && c.length > 0 && (this._selector = l, this._triggerArray.push(s))
                        }
                        this._parent = this._config.parent ? this._getParent() : null, this._config.parent || this._addAriaAndCollapsedClass(this._element, this._triggerArray), this._config.toggle && this.toggle()
                    }

                    var f = i.prototype;
                    return f.toggle = function () {
                        e(this._element).hasClass(u.SHOW) ? this.hide() : this.show()
                    }, f.show = function () {
                        var t, o, r = this;
                        if (!(this._isTransitioning || e(this._element).hasClass(u.SHOW) || (this._parent && 0 === (t = [].slice.call(this._parent.querySelectorAll(p.ACTIVES)).filter(function (e) {
                            return e.getAttribute("data-parent") === r._config.parent
                        })).length && (t = null), t && (o = e(t).not(this._selector).data(n)) && o._isTransitioning))) {
                            var s = e.Event(c.SHOW);
                            if (e(this._element).trigger(s), !s.isDefaultPrevented()) {
                                t && (i._jQueryInterface.call(e(t).not(this._selector), "hide"), o || e(t).data(n, null));
                                var l = this._getDimension();
                                e(this._element).removeClass(u.COLLAPSE).addClass(u.COLLAPSING), this._element.style[l] = 0, this._triggerArray.length && e(this._triggerArray).removeClass(u.COLLAPSED).attr("aria-expanded", !0), this.setTransitioning(!0);
                                var d = l[0].toUpperCase() + l.slice(1), f = "scroll" + d,
                                    h = a.getTransitionDurationFromElement(this._element);
                                e(this._element).one(a.TRANSITION_END, function () {
                                    e(r._element).removeClass(u.COLLAPSING).addClass(u.COLLAPSE).addClass(u.SHOW), r._element.style[l] = "", r.setTransitioning(!1), e(r._element).trigger(c.SHOWN)
                                }).emulateTransitionEnd(h), this._element.style[l] = this._element[f] + "px"
                            }
                        }
                    }, f.hide = function () {
                        var t = this;
                        if (!this._isTransitioning && e(this._element).hasClass(u.SHOW)) {
                            var n = e.Event(c.HIDE);
                            if (e(this._element).trigger(n), !n.isDefaultPrevented()) {
                                var i = this._getDimension();
                                this._element.style[i] = this._element.getBoundingClientRect()[i] + "px", a.reflow(this._element), e(this._element).addClass(u.COLLAPSING).removeClass(u.COLLAPSE).removeClass(u.SHOW);
                                var o = this._triggerArray.length;
                                if (o > 0) for (var r = 0; r < o; r++) {
                                    var s = this._triggerArray[r], l = a.getSelectorFromElement(s);
                                    if (null !== l) {
                                        var d = e([].slice.call(document.querySelectorAll(l)));
                                        d.hasClass(u.SHOW) || e(s).addClass(u.COLLAPSED).attr("aria-expanded", !1)
                                    }
                                }
                                this.setTransitioning(!0), this._element.style[i] = "";
                                var p = a.getTransitionDurationFromElement(this._element);
                                e(this._element).one(a.TRANSITION_END, function () {
                                    t.setTransitioning(!1), e(t._element).removeClass(u.COLLAPSING).addClass(u.COLLAPSE).trigger(c.HIDDEN)
                                }).emulateTransitionEnd(p)
                            }
                        }
                    }, f.setTransitioning = function (e) {
                        this._isTransitioning = e
                    }, f.dispose = function () {
                        e.removeData(this._element, n), this._config = null, this._parent = null, this._element = null, this._triggerArray = null, this._isTransitioning = null
                    }, f._getConfig = function (e) {
                        return (e = s({}, r, e)).toggle = Boolean(e.toggle), a.typeCheckConfig(t, e, l), e
                    }, f._getDimension = function () {
                        var t = e(this._element).hasClass(d.WIDTH);
                        return t ? d.WIDTH : d.HEIGHT
                    }, f._getParent = function () {
                        var t = this, n = null;
                        a.isElement(this._config.parent) ? (n = this._config.parent, void 0 !== this._config.parent.jquery && (n = this._config.parent[0])) : n = document.querySelector(this._config.parent);
                        var o = '[data-toggle="collapse"][data-parent="' + this._config.parent + '"]',
                            r = [].slice.call(n.querySelectorAll(o));
                        return e(r).each(function (e, n) {
                            t._addAriaAndCollapsedClass(i._getTargetFromElement(n), [n])
                        }), n
                    }, f._addAriaAndCollapsedClass = function (t, n) {
                        if (t) {
                            var i = e(t).hasClass(u.SHOW);
                            n.length && e(n).toggleClass(u.COLLAPSED, !i).attr("aria-expanded", i)
                        }
                    }, i._getTargetFromElement = function (e) {
                        var t = a.getSelectorFromElement(e);
                        return t ? document.querySelector(t) : null
                    }, i._jQueryInterface = function (t) {
                        return this.each(function () {
                            var o = e(this), a = o.data(n), l = s({}, r, o.data(), "object" == typeof t && t ? t : {});
                            if (!a && l.toggle && /show|hide/.test(t) && (l.toggle = !1), a || (a = new i(this, l), o.data(n, a)), "string" == typeof t) {
                                if (void 0 === a[t]) throw new TypeError('No method named "' + t + '"');
                                a[t]()
                            }
                        })
                    }, o(i, null, [{
                        key: "VERSION", get: function () {
                            return "4.1.2"
                        }
                    }, {
                        key: "Default", get: function () {
                            return r
                        }
                    }]), i
                }();
            return e(document).on(c.CLICK_DATA_API, p.DATA_TOGGLE, function (t) {
                "A" === t.currentTarget.tagName && t.preventDefault();
                var i = e(this), o = a.getSelectorFromElement(this), r = [].slice.call(document.querySelectorAll(o));
                e(r).each(function () {
                    var t = e(this), o = t.data(n), r = o ? "toggle" : i.data();
                    f._jQueryInterface.call(t, r)
                })
            }), e.fn[t] = f._jQueryInterface, e.fn[t].Constructor = f, e.fn[t].noConflict = function () {
                return e.fn[t] = i, f._jQueryInterface
            }, f
        }(t), p = function (e) {
            var t = "dropdown", i = "bs.dropdown", r = "." + i, l = e.fn[t], c = new RegExp("38|40|27"), u = {
                HIDE: "hide" + r,
                HIDDEN: "hidden" + r,
                SHOW: "show" + r,
                SHOWN: "shown" + r,
                CLICK: "click" + r,
                CLICK_DATA_API: "click.bs.dropdown.data-api",
                KEYDOWN_DATA_API: "keydown.bs.dropdown.data-api",
                KEYUP_DATA_API: "keyup.bs.dropdown.data-api"
            }, d = {
                DISABLED: "disabled",
                SHOW: "show",
                DROPUP: "dropup",
                DROPRIGHT: "dropright",
                DROPLEFT: "dropleft",
                MENURIGHT: "dropdown-menu-right",
                MENULEFT: "dropdown-menu-left",
                POSITION_STATIC: "position-static"
            }, p = {
                DATA_TOGGLE: '[data-toggle="dropdown"]',
                FORM_CHILD: ".dropdown form",
                MENU: ".dropdown-menu",
                NAVBAR_NAV: ".navbar-nav",
                VISIBLE_ITEMS: ".dropdown-menu .dropdown-item:not(.disabled):not(:disabled)"
            }, f = {
                TOP: "top-start",
                TOPEND: "top-end",
                BOTTOM: "bottom-start",
                BOTTOMEND: "bottom-end",
                RIGHT: "right-start",
                RIGHTEND: "right-end",
                LEFT: "left-start",
                LEFTEND: "left-end"
            }, h = {offset: 0, flip: !0, boundary: "scrollParent", reference: "toggle", display: "dynamic"}, m = {
                offset: "(number|string|function)",
                flip: "boolean",
                boundary: "(string|element)",
                reference: "(string|element)",
                display: "string"
            }, g = function () {
                function l(e, t) {
                    this._element = e, this._popper = null, this._config = this._getConfig(t), this._menu = this._getMenuElement(), this._inNavbar = this._detectNavbar(), this._addEventListeners()
                }

                var g = l.prototype;
                return g.toggle = function () {
                    if (!this._element.disabled && !e(this._element).hasClass(d.DISABLED)) {
                        var t = l._getParentFromElement(this._element), i = e(this._menu).hasClass(d.SHOW);
                        if (l._clearMenus(), !i) {
                            var o = {relatedTarget: this._element}, r = e.Event(u.SHOW, o);
                            if (e(t).trigger(r), !r.isDefaultPrevented()) {
                                if (!this._inNavbar) {
                                    if (void 0 === n) throw new TypeError("Bootstrap dropdown require Popper.js (https://popper.js.org)");
                                    var s = this._element;
                                    "parent" === this._config.reference ? s = t : a.isElement(this._config.reference) && (s = this._config.reference, void 0 !== this._config.reference.jquery && (s = this._config.reference[0])), "scrollParent" !== this._config.boundary && e(t).addClass(d.POSITION_STATIC), this._popper = new n(s, this._menu, this._getPopperConfig())
                                }
                                "ontouchstart" in document.documentElement && 0 === e(t).closest(p.NAVBAR_NAV).length && e(document.body).children().on("mouseover", null, e.noop), this._element.focus(), this._element.setAttribute("aria-expanded", !0), e(this._menu).toggleClass(d.SHOW), e(t).toggleClass(d.SHOW).trigger(e.Event(u.SHOWN, o))
                            }
                        }
                    }
                }, g.dispose = function () {
                    e.removeData(this._element, i), e(this._element).off(r), this._element = null, this._menu = null, null !== this._popper && (this._popper.destroy(), this._popper = null)
                }, g.update = function () {
                    this._inNavbar = this._detectNavbar(), null !== this._popper && this._popper.scheduleUpdate()
                }, g._addEventListeners = function () {
                    var t = this;
                    e(this._element).on(u.CLICK, function (e) {
                        e.preventDefault(), e.stopPropagation(), t.toggle()
                    })
                }, g._getConfig = function (n) {
                    return n = s({}, this.constructor.Default, e(this._element).data(), n), a.typeCheckConfig(t, n, this.constructor.DefaultType), n
                }, g._getMenuElement = function () {
                    if (!this._menu) {
                        var e = l._getParentFromElement(this._element);
                        e && (this._menu = e.querySelector(p.MENU))
                    }
                    return this._menu
                }, g._getPlacement = function () {
                    var t = e(this._element.parentNode), n = f.BOTTOM;
                    return t.hasClass(d.DROPUP) ? (n = f.TOP, e(this._menu).hasClass(d.MENURIGHT) && (n = f.TOPEND)) : t.hasClass(d.DROPRIGHT) ? n = f.RIGHT : t.hasClass(d.DROPLEFT) ? n = f.LEFT : e(this._menu).hasClass(d.MENURIGHT) && (n = f.BOTTOMEND), n
                }, g._detectNavbar = function () {
                    return e(this._element).closest(".navbar").length > 0
                }, g._getPopperConfig = function () {
                    var e = this, t = {};
                    "function" == typeof this._config.offset ? t.fn = function (t) {
                        return t.offsets = s({}, t.offsets, e._config.offset(t.offsets) || {}), t
                    } : t.offset = this._config.offset;
                    var n = {
                        placement: this._getPlacement(),
                        modifiers: {
                            offset: t,
                            flip: {enabled: this._config.flip},
                            preventOverflow: {boundariesElement: this._config.boundary}
                        }
                    };
                    return "static" === this._config.display && (n.modifiers.applyStyle = {enabled: !1}), n
                }, l._jQueryInterface = function (t) {
                    return this.each(function () {
                        var n = e(this).data(i), o = "object" == typeof t ? t : null;
                        if (n || (n = new l(this, o), e(this).data(i, n)), "string" == typeof t) {
                            if (void 0 === n[t]) throw new TypeError('No method named "' + t + '"');
                            n[t]()
                        }
                    })
                }, l._clearMenus = function (t) {
                    if (!t || 3 !== t.which && ("keyup" !== t.type || 9 === t.which)) for (var n = [].slice.call(document.querySelectorAll(p.DATA_TOGGLE)), o = 0, r = n.length; o < r; o++) {
                        var s = l._getParentFromElement(n[o]), a = e(n[o]).data(i), c = {relatedTarget: n[o]};
                        if (t && "click" === t.type && (c.clickEvent = t), a) {
                            var f = a._menu;
                            if (e(s).hasClass(d.SHOW) && !(t && ("click" === t.type && /input|textarea/i.test(t.target.tagName) || "keyup" === t.type && 9 === t.which) && e.contains(s, t.target))) {
                                var h = e.Event(u.HIDE, c);
                                e(s).trigger(h), h.isDefaultPrevented() || ("ontouchstart" in document.documentElement && e(document.body).children().off("mouseover", null, e.noop), n[o].setAttribute("aria-expanded", "false"), e(f).removeClass(d.SHOW), e(s).removeClass(d.SHOW).trigger(e.Event(u.HIDDEN, c)))
                            }
                        }
                    }
                }, l._getParentFromElement = function (e) {
                    var t, n = a.getSelectorFromElement(e);
                    return n && (t = document.querySelector(n)), t || e.parentNode
                }, l._dataApiKeydownHandler = function (t) {
                    if ((/input|textarea/i.test(t.target.tagName) ? !(32 === t.which || 27 !== t.which && (40 !== t.which && 38 !== t.which || e(t.target).closest(p.MENU).length)) : c.test(t.which)) && (t.preventDefault(), t.stopPropagation(), !this.disabled && !e(this).hasClass(d.DISABLED))) {
                        var n = l._getParentFromElement(this), i = e(n).hasClass(d.SHOW);
                        if ((i || 27 === t.which && 32 === t.which) && (!i || 27 !== t.which && 32 !== t.which)) {
                            var o = [].slice.call(n.querySelectorAll(p.VISIBLE_ITEMS));
                            if (0 !== o.length) {
                                var r = o.indexOf(t.target);
                                38 === t.which && r > 0 && r--, 40 === t.which && r < o.length - 1 && r++, r < 0 && (r = 0), o[r].focus()
                            }
                        } else {
                            if (27 === t.which) {
                                var s = n.querySelector(p.DATA_TOGGLE);
                                e(s).trigger("focus")
                            }
                            e(this).trigger("click")
                        }
                    }
                }, o(l, null, [{
                    key: "VERSION", get: function () {
                        return "4.1.2"
                    }
                }, {
                    key: "Default", get: function () {
                        return h
                    }
                }, {
                    key: "DefaultType", get: function () {
                        return m
                    }
                }]), l
            }();
            return e(document).on(u.KEYDOWN_DATA_API, p.DATA_TOGGLE, g._dataApiKeydownHandler).on(u.KEYDOWN_DATA_API, p.MENU, g._dataApiKeydownHandler).on(u.CLICK_DATA_API + " " + u.KEYUP_DATA_API, g._clearMenus).on(u.CLICK_DATA_API, p.DATA_TOGGLE, function (t) {
                t.preventDefault(), t.stopPropagation(), g._jQueryInterface.call(e(this), "toggle")
            }).on(u.CLICK_DATA_API, p.FORM_CHILD, function (e) {
                e.stopPropagation()
            }), e.fn[t] = g._jQueryInterface, e.fn[t].Constructor = g, e.fn[t].noConflict = function () {
                return e.fn[t] = l, g._jQueryInterface
            }, g
        }(t), f = function (e) {
            var t = "modal", n = ".bs.modal", i = e.fn.modal, r = {backdrop: !0, keyboard: !0, focus: !0, show: !0},
                l = {backdrop: "(boolean|string)", keyboard: "boolean", focus: "boolean", show: "boolean"}, c = {
                    HIDE: "hide.bs.modal",
                    HIDDEN: "hidden.bs.modal",
                    SHOW: "show.bs.modal",
                    SHOWN: "shown.bs.modal",
                    FOCUSIN: "focusin.bs.modal",
                    RESIZE: "resize.bs.modal",
                    CLICK_DISMISS: "click.dismiss.bs.modal",
                    KEYDOWN_DISMISS: "keydown.dismiss.bs.modal",
                    MOUSEUP_DISMISS: "mouseup.dismiss.bs.modal",
                    MOUSEDOWN_DISMISS: "mousedown.dismiss.bs.modal",
                    CLICK_DATA_API: "click.bs.modal.data-api"
                }, u = {
                    SCROLLBAR_MEASURER: "modal-scrollbar-measure",
                    BACKDROP: "modal-backdrop",
                    OPEN: "modal-open",
                    FADE: "fade",
                    SHOW: "show"
                }, d = {
                    DIALOG: ".modal-dialog",
                    DATA_TOGGLE: '[data-toggle="modal"]',
                    DATA_DISMISS: '[data-dismiss="modal"]',
                    FIXED_CONTENT: ".fixed-top, .fixed-bottom, .is-fixed, .sticky-top",
                    STICKY_CONTENT: ".sticky-top"
                }, p = function () {
                    function i(e, t) {
                        this._config = this._getConfig(t), this._element = e, this._dialog = e.querySelector(d.DIALOG), this._backdrop = null, this._isShown = !1, this._isBodyOverflowing = !1, this._ignoreBackdropClick = !1, this._scrollbarWidth = 0
                    }

                    var p = i.prototype;
                    return p.toggle = function (e) {
                        return this._isShown ? this.hide() : this.show(e)
                    }, p.show = function (t) {
                        var n = this;
                        if (!this._isTransitioning && !this._isShown) {
                            e(this._element).hasClass(u.FADE) && (this._isTransitioning = !0);
                            var i = e.Event(c.SHOW, {relatedTarget: t});
                            e(this._element).trigger(i), this._isShown || i.isDefaultPrevented() || (this._isShown = !0, this._checkScrollbar(), this._setScrollbar(), this._adjustDialog(), e(document.body).addClass(u.OPEN), this._setEscapeEvent(), this._setResizeEvent(), e(this._element).on(c.CLICK_DISMISS, d.DATA_DISMISS, function (e) {
                                return n.hide(e)
                            }), e(this._dialog).on(c.MOUSEDOWN_DISMISS, function () {
                                e(n._element).one(c.MOUSEUP_DISMISS, function (t) {
                                    e(t.target).is(n._element) && (n._ignoreBackdropClick = !0)
                                })
                            }), this._showBackdrop(function () {
                                return n._showElement(t)
                            }))
                        }
                    }, p.hide = function (t) {
                        var n = this;
                        if (t && t.preventDefault(), !this._isTransitioning && this._isShown) {
                            var i = e.Event(c.HIDE);
                            if (e(this._element).trigger(i), this._isShown && !i.isDefaultPrevented()) {
                                this._isShown = !1;
                                var o = e(this._element).hasClass(u.FADE);
                                if (o && (this._isTransitioning = !0), this._setEscapeEvent(), this._setResizeEvent(), e(document).off(c.FOCUSIN), e(this._element).removeClass(u.SHOW), e(this._element).off(c.CLICK_DISMISS), e(this._dialog).off(c.MOUSEDOWN_DISMISS), o) {
                                    var r = a.getTransitionDurationFromElement(this._element);
                                    e(this._element).one(a.TRANSITION_END, function (e) {
                                        return n._hideModal(e)
                                    }).emulateTransitionEnd(r)
                                } else this._hideModal()
                            }
                        }
                    }, p.dispose = function () {
                        e.removeData(this._element, "bs.modal"), e(window, document, this._element, this._backdrop).off(n), this._config = null, this._element = null, this._dialog = null, this._backdrop = null, this._isShown = null, this._isBodyOverflowing = null, this._ignoreBackdropClick = null, this._scrollbarWidth = null
                    }, p.handleUpdate = function () {
                        this._adjustDialog()
                    }, p._getConfig = function (e) {
                        return e = s({}, r, e), a.typeCheckConfig(t, e, l), e
                    }, p._showElement = function (t) {
                        var n = this, i = e(this._element).hasClass(u.FADE);
                        this._element.parentNode && this._element.parentNode.nodeType === Node.ELEMENT_NODE || document.body.appendChild(this._element), this._element.style.display = "block", this._element.removeAttribute("aria-hidden"), this._element.scrollTop = 0, i && a.reflow(this._element), e(this._element).addClass(u.SHOW), this._config.focus && this._enforceFocus();
                        var o = e.Event(c.SHOWN, {relatedTarget: t}), r = function () {
                            n._config.focus && n._element.focus(), n._isTransitioning = !1, e(n._element).trigger(o)
                        };
                        if (i) {
                            var s = a.getTransitionDurationFromElement(this._element);
                            e(this._dialog).one(a.TRANSITION_END, r).emulateTransitionEnd(s)
                        } else r()
                    }, p._enforceFocus = function () {
                        var t = this;
                        e(document).off(c.FOCUSIN).on(c.FOCUSIN, function (n) {
                            document !== n.target && t._element !== n.target && 0 === e(t._element).has(n.target).length && t._element.focus()
                        })
                    }, p._setEscapeEvent = function () {
                        var t = this;
                        this._isShown && this._config.keyboard ? e(this._element).on(c.KEYDOWN_DISMISS, function (e) {
                            27 === e.which && (e.preventDefault(), t.hide())
                        }) : this._isShown || e(this._element).off(c.KEYDOWN_DISMISS)
                    }, p._setResizeEvent = function () {
                        var t = this;
                        this._isShown ? e(window).on(c.RESIZE, function (e) {
                            return t.handleUpdate(e)
                        }) : e(window).off(c.RESIZE)
                    }, p._hideModal = function () {
                        var t = this;
                        this._element.style.display = "none", this._element.setAttribute("aria-hidden", !0), this._isTransitioning = !1, this._showBackdrop(function () {
                            e(document.body).removeClass(u.OPEN), t._resetAdjustments(), t._resetScrollbar(), e(t._element).trigger(c.HIDDEN)
                        })
                    }, p._removeBackdrop = function () {
                        this._backdrop && (e(this._backdrop).remove(), this._backdrop = null)
                    }, p._showBackdrop = function (t) {
                        var n = this, i = e(this._element).hasClass(u.FADE) ? u.FADE : "";
                        if (this._isShown && this._config.backdrop) {
                            if (this._backdrop = document.createElement("div"), this._backdrop.className = u.BACKDROP, i && this._backdrop.classList.add(i), e(this._backdrop).appendTo(document.body), e(this._element).on(c.CLICK_DISMISS, function (e) {
                                n._ignoreBackdropClick ? n._ignoreBackdropClick = !1 : e.target === e.currentTarget && ("static" === n._config.backdrop ? n._element.focus() : n.hide())
                            }), i && a.reflow(this._backdrop), e(this._backdrop).addClass(u.SHOW), !t) return;
                            if (!i) return void t();
                            var o = a.getTransitionDurationFromElement(this._backdrop);
                            e(this._backdrop).one(a.TRANSITION_END, t).emulateTransitionEnd(o)
                        } else if (!this._isShown && this._backdrop) {
                            e(this._backdrop).removeClass(u.SHOW);
                            var r = function () {
                                n._removeBackdrop(), t && t()
                            };
                            if (e(this._element).hasClass(u.FADE)) {
                                var s = a.getTransitionDurationFromElement(this._backdrop);
                                e(this._backdrop).one(a.TRANSITION_END, r).emulateTransitionEnd(s)
                            } else r()
                        } else t && t()
                    }, p._adjustDialog = function () {
                        var e = this._element.scrollHeight > document.documentElement.clientHeight;
                        !this._isBodyOverflowing && e && (this._element.style.paddingLeft = this._scrollbarWidth + "px"), this._isBodyOverflowing && !e && (this._element.style.paddingRight = this._scrollbarWidth + "px")
                    }, p._resetAdjustments = function () {
                        this._element.style.paddingLeft = "", this._element.style.paddingRight = ""
                    }, p._checkScrollbar = function () {
                        var e = document.body.getBoundingClientRect();
                        this._isBodyOverflowing = e.left + e.right < window.innerWidth, this._scrollbarWidth = this._getScrollbarWidth()
                    }, p._setScrollbar = function () {
                        var t = this;
                        if (this._isBodyOverflowing) {
                            var n = [].slice.call(document.querySelectorAll(d.FIXED_CONTENT)),
                                i = [].slice.call(document.querySelectorAll(d.STICKY_CONTENT));
                            e(n).each(function (n, i) {
                                var o = i.style.paddingRight, r = e(i).css("padding-right");
                                e(i).data("padding-right", o).css("padding-right", parseFloat(r) + t._scrollbarWidth + "px")
                            }), e(i).each(function (n, i) {
                                var o = i.style.marginRight, r = e(i).css("margin-right");
                                e(i).data("margin-right", o).css("margin-right", parseFloat(r) - t._scrollbarWidth + "px")
                            });
                            var o = document.body.style.paddingRight, r = e(document.body).css("padding-right");
                            e(document.body).data("padding-right", o).css("padding-right", parseFloat(r) + this._scrollbarWidth + "px")
                        }
                    }, p._resetScrollbar = function () {
                        var t = [].slice.call(document.querySelectorAll(d.FIXED_CONTENT));
                        e(t).each(function (t, n) {
                            var i = e(n).data("padding-right");
                            e(n).removeData("padding-right"), n.style.paddingRight = i || ""
                        });
                        var n = [].slice.call(document.querySelectorAll("" + d.STICKY_CONTENT));
                        e(n).each(function (t, n) {
                            var i = e(n).data("margin-right");
                            void 0 !== i && e(n).css("margin-right", i).removeData("margin-right")
                        });
                        var i = e(document.body).data("padding-right");
                        e(document.body).removeData("padding-right"), document.body.style.paddingRight = i || ""
                    }, p._getScrollbarWidth = function () {
                        var e = document.createElement("div");
                        e.className = u.SCROLLBAR_MEASURER, document.body.appendChild(e);
                        var t = e.getBoundingClientRect().width - e.clientWidth;
                        return document.body.removeChild(e), t
                    }, i._jQueryInterface = function (t, n) {
                        return this.each(function () {
                            var o = e(this).data("bs.modal"),
                                a = s({}, r, e(this).data(), "object" == typeof t && t ? t : {});
                            if (o || (o = new i(this, a), e(this).data("bs.modal", o)), "string" == typeof t) {
                                if (void 0 === o[t]) throw new TypeError('No method named "' + t + '"');
                                o[t](n)
                            } else a.show && o.show(n)
                        })
                    }, o(i, null, [{
                        key: "VERSION", get: function () {
                            return "4.1.2"
                        }
                    }, {
                        key: "Default", get: function () {
                            return r
                        }
                    }]), i
                }();
            return e(document).on(c.CLICK_DATA_API, d.DATA_TOGGLE, function (t) {
                var n, i = this, o = a.getSelectorFromElement(this);
                o && (n = document.querySelector(o));
                var r = e(n).data("bs.modal") ? "toggle" : s({}, e(n).data(), e(this).data());
                "A" !== this.tagName && "AREA" !== this.tagName || t.preventDefault();
                var l = e(n).one(c.SHOW, function (t) {
                    t.isDefaultPrevented() || l.one(c.HIDDEN, function () {
                        e(i).is(":visible") && i.focus()
                    })
                });
                p._jQueryInterface.call(e(n), r, this)
            }), e.fn.modal = p._jQueryInterface, e.fn.modal.Constructor = p, e.fn.modal.noConflict = function () {
                return e.fn.modal = i, p._jQueryInterface
            }, p
        }(t), h = function (e) {
            var t = "tooltip", i = ".bs.tooltip", r = e.fn[t], l = new RegExp("(^|\\s)bs-tooltip\\S+", "g"), c = {
                    animation: "boolean",
                    template: "string",
                    title: "(string|element|function)",
                    trigger: "string",
                    delay: "(number|object)",
                    html: "boolean",
                    selector: "(string|boolean)",
                    placement: "(string|function)",
                    offset: "(number|string)",
                    container: "(string|element|boolean)",
                    fallbackPlacement: "(string|array)",
                    boundary: "(string|element)"
                }, u = {AUTO: "auto", TOP: "top", RIGHT: "right", BOTTOM: "bottom", LEFT: "left"}, d = {
                    animation: !0,
                    template: '<div class="tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
                    trigger: "hover focus",
                    title: "",
                    delay: 0,
                    html: !1,
                    selector: !1,
                    placement: "top",
                    offset: 0,
                    container: !1,
                    fallbackPlacement: "flip",
                    boundary: "scrollParent"
                }, p = {SHOW: "show", OUT: "out"}, f = {
                    HIDE: "hide" + i,
                    HIDDEN: "hidden" + i,
                    SHOW: "show" + i,
                    SHOWN: "shown" + i,
                    INSERTED: "inserted" + i,
                    CLICK: "click" + i,
                    FOCUSIN: "focusin" + i,
                    FOCUSOUT: "focusout" + i,
                    MOUSEENTER: "mouseenter" + i,
                    MOUSELEAVE: "mouseleave" + i
                }, h = {FADE: "fade", SHOW: "show"},
                m = {TOOLTIP: ".tooltip", TOOLTIP_INNER: ".tooltip-inner", ARROW: ".arrow"},
                g = {HOVER: "hover", FOCUS: "focus", CLICK: "click", MANUAL: "manual"}, v = function () {
                    function r(e, t) {
                        if (void 0 === n) throw new TypeError("Bootstrap tooltips require Popper.js (https://popper.js.org)");
                        this._isEnabled = !0, this._timeout = 0, this._hoverState = "", this._activeTrigger = {}, this._popper = null, this.element = e, this.config = this._getConfig(t), this.tip = null, this._setListeners()
                    }

                    var v = r.prototype;
                    return v.enable = function () {
                        this._isEnabled = !0
                    }, v.disable = function () {
                        this._isEnabled = !1
                    }, v.toggleEnabled = function () {
                        this._isEnabled = !this._isEnabled
                    }, v.toggle = function (t) {
                        if (this._isEnabled) if (t) {
                            var n = this.constructor.DATA_KEY, i = e(t.currentTarget).data(n);
                            i || (i = new this.constructor(t.currentTarget, this._getDelegateConfig()), e(t.currentTarget).data(n, i)), i._activeTrigger.click = !i._activeTrigger.click, i._isWithActiveTrigger() ? i._enter(null, i) : i._leave(null, i)
                        } else {
                            if (e(this.getTipElement()).hasClass(h.SHOW)) return void this._leave(null, this);
                            this._enter(null, this)
                        }
                    }, v.dispose = function () {
                        clearTimeout(this._timeout), e.removeData(this.element, this.constructor.DATA_KEY), e(this.element).off(this.constructor.EVENT_KEY), e(this.element).closest(".modal").off("hide.bs.modal"), this.tip && e(this.tip).remove(), this._isEnabled = null, this._timeout = null, this._hoverState = null, this._activeTrigger = null, null !== this._popper && this._popper.destroy(), this._popper = null, this.element = null, this.config = null, this.tip = null
                    }, v.show = function () {
                        var t = this;
                        if ("none" === e(this.element).css("display")) throw new Error("Please use show on visible elements");
                        var i = e.Event(this.constructor.Event.SHOW);
                        if (this.isWithContent() && this._isEnabled) {
                            e(this.element).trigger(i);
                            var o = e.contains(this.element.ownerDocument.documentElement, this.element);
                            if (i.isDefaultPrevented() || !o) return;
                            var r = this.getTipElement(), s = a.getUID(this.constructor.NAME);
                            r.setAttribute("id", s), this.element.setAttribute("aria-describedby", s), this.setContent(), this.config.animation && e(r).addClass(h.FADE);
                            var l = "function" == typeof this.config.placement ? this.config.placement.call(this, r, this.element) : this.config.placement,
                                c = this._getAttachment(l);
                            this.addAttachmentClass(c);
                            var u = !1 === this.config.container ? document.body : e(document).find(this.config.container);
                            e(r).data(this.constructor.DATA_KEY, this), e.contains(this.element.ownerDocument.documentElement, this.tip) || e(r).appendTo(u), e(this.element).trigger(this.constructor.Event.INSERTED), this._popper = new n(this.element, r, {
                                placement: c,
                                modifiers: {
                                    offset: {offset: this.config.offset},
                                    flip: {behavior: this.config.fallbackPlacement},
                                    arrow: {element: m.ARROW},
                                    preventOverflow: {boundariesElement: this.config.boundary}
                                },
                                onCreate: function (e) {
                                    e.originalPlacement !== e.placement && t._handlePopperPlacementChange(e)
                                },
                                onUpdate: function (e) {
                                    t._handlePopperPlacementChange(e)
                                }
                            }), e(r).addClass(h.SHOW), "ontouchstart" in document.documentElement && e(document.body).children().on("mouseover", null, e.noop);
                            var d = function () {
                                t.config.animation && t._fixTransition();
                                var n = t._hoverState;
                                t._hoverState = null, e(t.element).trigger(t.constructor.Event.SHOWN), n === p.OUT && t._leave(null, t)
                            };
                            if (e(this.tip).hasClass(h.FADE)) {
                                var f = a.getTransitionDurationFromElement(this.tip);
                                e(this.tip).one(a.TRANSITION_END, d).emulateTransitionEnd(f)
                            } else d()
                        }
                    }, v.hide = function (t) {
                        var n = this, i = this.getTipElement(), o = e.Event(this.constructor.Event.HIDE), r = function () {
                            n._hoverState !== p.SHOW && i.parentNode && i.parentNode.removeChild(i), n._cleanTipClass(), n.element.removeAttribute("aria-describedby"), e(n.element).trigger(n.constructor.Event.HIDDEN), null !== n._popper && n._popper.destroy(), t && t()
                        };
                        if (e(this.element).trigger(o), !o.isDefaultPrevented()) {
                            if (e(i).removeClass(h.SHOW), "ontouchstart" in document.documentElement && e(document.body).children().off("mouseover", null, e.noop), this._activeTrigger[g.CLICK] = !1, this._activeTrigger[g.FOCUS] = !1, this._activeTrigger[g.HOVER] = !1, e(this.tip).hasClass(h.FADE)) {
                                var s = a.getTransitionDurationFromElement(i);
                                e(i).one(a.TRANSITION_END, r).emulateTransitionEnd(s)
                            } else r();
                            this._hoverState = ""
                        }
                    }, v.update = function () {
                        null !== this._popper && this._popper.scheduleUpdate()
                    }, v.isWithContent = function () {
                        return Boolean(this.getTitle())
                    }, v.addAttachmentClass = function (t) {
                        e(this.getTipElement()).addClass("bs-tooltip-" + t)
                    }, v.getTipElement = function () {
                        return this.tip = this.tip || e(this.config.template)[0], this.tip
                    }, v.setContent = function () {
                        var t = this.getTipElement();
                        this.setElementContent(e(t.querySelectorAll(m.TOOLTIP_INNER)), this.getTitle()), e(t).removeClass(h.FADE + " " + h.SHOW)
                    }, v.setElementContent = function (t, n) {
                        var i = this.config.html;
                        "object" == typeof n && (n.nodeType || n.jquery) ? i ? e(n).parent().is(t) || t.empty().append(n) : t.text(e(n).text()) : t[i ? "html" : "text"](n)
                    }, v.getTitle = function () {
                        var e = this.element.getAttribute("data-original-title");
                        return e || (e = "function" == typeof this.config.title ? this.config.title.call(this.element) : this.config.title), e
                    }, v._getAttachment = function (e) {
                        return u[e.toUpperCase()]
                    }, v._setListeners = function () {
                        var t = this, n = this.config.trigger.split(" ");
                        n.forEach(function (n) {
                            if ("click" === n) e(t.element).on(t.constructor.Event.CLICK, t.config.selector, function (e) {
                                return t.toggle(e)
                            }); else if (n !== g.MANUAL) {
                                var i = n === g.HOVER ? t.constructor.Event.MOUSEENTER : t.constructor.Event.FOCUSIN,
                                    o = n === g.HOVER ? t.constructor.Event.MOUSELEAVE : t.constructor.Event.FOCUSOUT;
                                e(t.element).on(i, t.config.selector, function (e) {
                                    return t._enter(e)
                                }).on(o, t.config.selector, function (e) {
                                    return t._leave(e)
                                })
                            }
                            e(t.element).closest(".modal").on("hide.bs.modal", function () {
                                return t.hide()
                            })
                        }), this.config.selector ? this.config = s({}, this.config, {
                            trigger: "manual",
                            selector: ""
                        }) : this._fixTitle()
                    }, v._fixTitle = function () {
                        var e = typeof this.element.getAttribute("data-original-title");
                        (this.element.getAttribute("title") || "string" !== e) && (this.element.setAttribute("data-original-title", this.element.getAttribute("title") || ""), this.element.setAttribute("title", ""))
                    }, v._enter = function (t, n) {
                        var i = this.constructor.DATA_KEY;
                        (n = n || e(t.currentTarget).data(i)) || (n = new this.constructor(t.currentTarget, this._getDelegateConfig()), e(t.currentTarget).data(i, n)), t && (n._activeTrigger["focusin" === t.type ? g.FOCUS : g.HOVER] = !0), e(n.getTipElement()).hasClass(h.SHOW) || n._hoverState === p.SHOW ? n._hoverState = p.SHOW : (clearTimeout(n._timeout), n._hoverState = p.SHOW, n.config.delay && n.config.delay.show ? n._timeout = setTimeout(function () {
                            n._hoverState === p.SHOW && n.show()
                        }, n.config.delay.show) : n.show())
                    }, v._leave = function (t, n) {
                        var i = this.constructor.DATA_KEY;
                        (n = n || e(t.currentTarget).data(i)) || (n = new this.constructor(t.currentTarget, this._getDelegateConfig()), e(t.currentTarget).data(i, n)), t && (n._activeTrigger["focusout" === t.type ? g.FOCUS : g.HOVER] = !1), n._isWithActiveTrigger() || (clearTimeout(n._timeout), n._hoverState = p.OUT, n.config.delay && n.config.delay.hide ? n._timeout = setTimeout(function () {
                            n._hoverState === p.OUT && n.hide()
                        }, n.config.delay.hide) : n.hide())
                    }, v._isWithActiveTrigger = function () {
                        for (var e in this._activeTrigger) if (this._activeTrigger[e]) return !0;
                        return !1
                    }, v._getConfig = function (n) {
                        return "number" == typeof (n = s({}, this.constructor.Default, e(this.element).data(), "object" == typeof n && n ? n : {})).delay && (n.delay = {
                            show: n.delay,
                            hide: n.delay
                        }), "number" == typeof n.title && (n.title = n.title.toString()), "number" == typeof n.content && (n.content = n.content.toString()), a.typeCheckConfig(t, n, this.constructor.DefaultType), n
                    }, v._getDelegateConfig = function () {
                        var e = {};
                        if (this.config) for (var t in this.config) this.constructor.Default[t] !== this.config[t] && (e[t] = this.config[t]);
                        return e
                    }, v._cleanTipClass = function () {
                        var t = e(this.getTipElement()), n = t.attr("class").match(l);
                        null !== n && n.length && t.removeClass(n.join(""))
                    }, v._handlePopperPlacementChange = function (e) {
                        var t = e.instance;
                        this.tip = t.popper, this._cleanTipClass(), this.addAttachmentClass(this._getAttachment(e.placement))
                    }, v._fixTransition = function () {
                        var t = this.getTipElement(), n = this.config.animation;
                        null === t.getAttribute("x-placement") && (e(t).removeClass(h.FADE), this.config.animation = !1, this.hide(), this.show(), this.config.animation = n)
                    }, r._jQueryInterface = function (t) {
                        return this.each(function () {
                            var n = e(this).data("bs.tooltip"), i = "object" == typeof t && t;
                            if ((n || !/dispose|hide/.test(t)) && (n || (n = new r(this, i), e(this).data("bs.tooltip", n)), "string" == typeof t)) {
                                if (void 0 === n[t]) throw new TypeError('No method named "' + t + '"');
                                n[t]()
                            }
                        })
                    }, o(r, null, [{
                        key: "VERSION", get: function () {
                            return "4.1.2"
                        }
                    }, {
                        key: "Default", get: function () {
                            return d
                        }
                    }, {
                        key: "NAME", get: function () {
                            return t
                        }
                    }, {
                        key: "DATA_KEY", get: function () {
                            return "bs.tooltip"
                        }
                    }, {
                        key: "Event", get: function () {
                            return f
                        }
                    }, {
                        key: "EVENT_KEY", get: function () {
                            return i
                        }
                    }, {
                        key: "DefaultType", get: function () {
                            return c
                        }
                    }]), r
                }();
            return e.fn[t] = v._jQueryInterface, e.fn[t].Constructor = v, e.fn[t].noConflict = function () {
                return e.fn[t] = r, v._jQueryInterface
            }, v
        }(t), m = function (e) {
            var t = "popover", n = ".bs.popover", i = e.fn[t], r = new RegExp("(^|\\s)bs-popover\\S+", "g"),
                a = s({}, h.Default, {
                    placement: "right",
                    trigger: "click",
                    content: "",
                    template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
                }), l = s({}, h.DefaultType, {content: "(string|element|function)"}), c = {FADE: "fade", SHOW: "show"},
                u = {TITLE: ".popover-header", CONTENT: ".popover-body"}, d = {
                    HIDE: "hide" + n,
                    HIDDEN: "hidden" + n,
                    SHOW: "show" + n,
                    SHOWN: "shown" + n,
                    INSERTED: "inserted" + n,
                    CLICK: "click" + n,
                    FOCUSIN: "focusin" + n,
                    FOCUSOUT: "focusout" + n,
                    MOUSEENTER: "mouseenter" + n,
                    MOUSELEAVE: "mouseleave" + n
                }, p = function (i) {
                    function s() {
                        return i.apply(this, arguments) || this
                    }

                    !function (e, t) {
                        e.prototype = Object.create(t.prototype), e.prototype.constructor = e, e.__proto__ = t
                    }(s, i);
                    var p = s.prototype;
                    return p.isWithContent = function () {
                        return this.getTitle() || this._getContent()
                    }, p.addAttachmentClass = function (t) {
                        e(this.getTipElement()).addClass("bs-popover-" + t)
                    }, p.getTipElement = function () {
                        return this.tip = this.tip || e(this.config.template)[0], this.tip
                    }, p.setContent = function () {
                        var t = e(this.getTipElement());
                        this.setElementContent(t.find(u.TITLE), this.getTitle());
                        var n = this._getContent();
                        "function" == typeof n && (n = n.call(this.element)), this.setElementContent(t.find(u.CONTENT), n), t.removeClass(c.FADE + " " + c.SHOW)
                    }, p._getContent = function () {
                        return this.element.getAttribute("data-content") || this.config.content
                    }, p._cleanTipClass = function () {
                        var t = e(this.getTipElement()), n = t.attr("class").match(r);
                        null !== n && n.length > 0 && t.removeClass(n.join(""))
                    }, s._jQueryInterface = function (t) {
                        return this.each(function () {
                            var n = e(this).data("bs.popover"), i = "object" == typeof t ? t : null;
                            if ((n || !/destroy|hide/.test(t)) && (n || (n = new s(this, i), e(this).data("bs.popover", n)), "string" == typeof t)) {
                                if (void 0 === n[t]) throw new TypeError('No method named "' + t + '"');
                                n[t]()
                            }
                        })
                    }, o(s, null, [{
                        key: "VERSION", get: function () {
                            return "4.1.2"
                        }
                    }, {
                        key: "Default", get: function () {
                            return a
                        }
                    }, {
                        key: "NAME", get: function () {
                            return t
                        }
                    }, {
                        key: "DATA_KEY", get: function () {
                            return "bs.popover"
                        }
                    }, {
                        key: "Event", get: function () {
                            return d
                        }
                    }, {
                        key: "EVENT_KEY", get: function () {
                            return n
                        }
                    }, {
                        key: "DefaultType", get: function () {
                            return l
                        }
                    }]), s
                }(h);
            return e.fn[t] = p._jQueryInterface, e.fn[t].Constructor = p, e.fn[t].noConflict = function () {
                return e.fn[t] = i, p._jQueryInterface
            }, p
        }(t), g = function (e) {
            var t = "scrollspy", n = e.fn[t], i = {offset: 10, method: "auto", target: ""},
                r = {offset: "number", method: "string", target: "(string|element)"}, l = {
                    ACTIVATE: "activate.bs.scrollspy",
                    SCROLL: "scroll.bs.scrollspy",
                    LOAD_DATA_API: "load.bs.scrollspy.data-api"
                }, c = {DROPDOWN_ITEM: "dropdown-item", DROPDOWN_MENU: "dropdown-menu", ACTIVE: "active"}, u = {
                    DATA_SPY: '[data-spy="scroll"]',
                    ACTIVE: ".active",
                    NAV_LIST_GROUP: ".nav, .list-group",
                    NAV_LINKS: ".nav-link",
                    NAV_ITEMS: ".nav-item",
                    LIST_ITEMS: ".list-group-item",
                    DROPDOWN: ".dropdown",
                    DROPDOWN_ITEMS: ".dropdown-item",
                    DROPDOWN_TOGGLE: ".dropdown-toggle"
                }, d = {OFFSET: "offset", POSITION: "position"}, p = function () {
                    function n(t, n) {
                        var i = this;
                        this._element = t, this._scrollElement = "BODY" === t.tagName ? window : t, this._config = this._getConfig(n), this._selector = this._config.target + " " + u.NAV_LINKS + "," + this._config.target + " " + u.LIST_ITEMS + "," + this._config.target + " " + u.DROPDOWN_ITEMS, this._offsets = [], this._targets = [], this._activeTarget = null, this._scrollHeight = 0, e(this._scrollElement).on(l.SCROLL, function (e) {
                            return i._process(e)
                        }), this.refresh(), this._process()
                    }

                    var p = n.prototype;
                    return p.refresh = function () {
                        var t = this, n = this._scrollElement === this._scrollElement.window ? d.OFFSET : d.POSITION,
                            i = "auto" === this._config.method ? n : this._config.method,
                            o = i === d.POSITION ? this._getScrollTop() : 0;
                        this._offsets = [], this._targets = [], this._scrollHeight = this._getScrollHeight();
                        var r = [].slice.call(document.querySelectorAll(this._selector));
                        r.map(function (t) {
                            var n, r = a.getSelectorFromElement(t);
                            if (r && (n = document.querySelector(r)), n) {
                                var s = n.getBoundingClientRect();
                                if (s.width || s.height) return [e(n)[i]().top + o, r]
                            }
                            return null
                        }).filter(function (e) {
                            return e
                        }).sort(function (e, t) {
                            return e[0] - t[0]
                        }).forEach(function (e) {
                            t._offsets.push(e[0]), t._targets.push(e[1])
                        })
                    }, p.dispose = function () {
                        e.removeData(this._element, "bs.scrollspy"), e(this._scrollElement).off(".bs.scrollspy"), this._element = null, this._scrollElement = null, this._config = null, this._selector = null, this._offsets = null, this._targets = null, this._activeTarget = null, this._scrollHeight = null
                    }, p._getConfig = function (n) {
                        if ("string" != typeof (n = s({}, i, "object" == typeof n && n ? n : {})).target) {
                            var o = e(n.target).attr("id");
                            o || (o = a.getUID(t), e(n.target).attr("id", o)), n.target = "#" + o
                        }
                        return a.typeCheckConfig(t, n, r), n
                    }, p._getScrollTop = function () {
                        return this._scrollElement === window ? this._scrollElement.pageYOffset : this._scrollElement.scrollTop
                    }, p._getScrollHeight = function () {
                        return this._scrollElement.scrollHeight || Math.max(document.body.scrollHeight, document.documentElement.scrollHeight)
                    }, p._getOffsetHeight = function () {
                        return this._scrollElement === window ? window.innerHeight : this._scrollElement.getBoundingClientRect().height
                    }, p._process = function () {
                        var e = this._getScrollTop() + this._config.offset, t = this._getScrollHeight(),
                            n = this._config.offset + t - this._getOffsetHeight();
                        if (this._scrollHeight !== t && this.refresh(), e >= n) {
                            var i = this._targets[this._targets.length - 1];
                            this._activeTarget !== i && this._activate(i)
                        } else {
                            if (this._activeTarget && e < this._offsets[0] && this._offsets[0] > 0) return this._activeTarget = null, void this._clear();
                            for (var o = this._offsets.length, r = o; r--;) {
                                var s = this._activeTarget !== this._targets[r] && e >= this._offsets[r] && (void 0 === this._offsets[r + 1] || e < this._offsets[r + 1]);
                                s && this._activate(this._targets[r])
                            }
                        }
                    }, p._activate = function (t) {
                        this._activeTarget = t, this._clear();
                        var n = this._selector.split(",");
                        n = n.map(function (e) {
                            return e + '[data-target="' + t + '"],' + e + '[href="' + t + '"]'
                        });
                        var i = e([].slice.call(document.querySelectorAll(n.join(","))));
                        i.hasClass(c.DROPDOWN_ITEM) ? (i.closest(u.DROPDOWN).find(u.DROPDOWN_TOGGLE).addClass(c.ACTIVE), i.addClass(c.ACTIVE)) : (i.addClass(c.ACTIVE), i.parents(u.NAV_LIST_GROUP).prev(u.NAV_LINKS + ", " + u.LIST_ITEMS).addClass(c.ACTIVE), i.parents(u.NAV_LIST_GROUP).prev(u.NAV_ITEMS).children(u.NAV_LINKS).addClass(c.ACTIVE)), e(this._scrollElement).trigger(l.ACTIVATE, {relatedTarget: t})
                    }, p._clear = function () {
                        var t = [].slice.call(document.querySelectorAll(this._selector));
                        e(t).filter(u.ACTIVE).removeClass(c.ACTIVE)
                    }, n._jQueryInterface = function (t) {
                        return this.each(function () {
                            var i = e(this).data("bs.scrollspy"), o = "object" == typeof t && t;
                            if (i || (i = new n(this, o), e(this).data("bs.scrollspy", i)), "string" == typeof t) {
                                if (void 0 === i[t]) throw new TypeError('No method named "' + t + '"');
                                i[t]()
                            }
                        })
                    }, o(n, null, [{
                        key: "VERSION", get: function () {
                            return "4.1.2"
                        }
                    }, {
                        key: "Default", get: function () {
                            return i
                        }
                    }]), n
                }();
            return e(window).on(l.LOAD_DATA_API, function () {
                for (var t = [].slice.call(document.querySelectorAll(u.DATA_SPY)), n = t.length, i = n; i--;) {
                    var o = e(t[i]);
                    p._jQueryInterface.call(o, o.data())
                }
            }), e.fn[t] = p._jQueryInterface, e.fn[t].Constructor = p, e.fn[t].noConflict = function () {
                return e.fn[t] = n, p._jQueryInterface
            }, p
        }(t), v = function (e) {
            var t = e.fn.tab, n = {
                    HIDE: "hide.bs.tab",
                    HIDDEN: "hidden.bs.tab",
                    SHOW: "show.bs.tab",
                    SHOWN: "shown.bs.tab",
                    CLICK_DATA_API: "click.bs.tab.data-api"
                }, i = {DROPDOWN_MENU: "dropdown-menu", ACTIVE: "active", DISABLED: "disabled", FADE: "fade", SHOW: "show"},
                r = {
                    DROPDOWN: ".dropdown",
                    NAV_LIST_GROUP: ".nav, .list-group",
                    ACTIVE: ".active",
                    ACTIVE_UL: "> li > .active",
                    DATA_TOGGLE: '[data-toggle="tab"], [data-toggle="pill"], [data-toggle="list"]',
                    DROPDOWN_TOGGLE: ".dropdown-toggle",
                    DROPDOWN_ACTIVE_CHILD: "> .dropdown-menu .active"
                }, s = function () {
                    function t(e) {
                        this._element = e
                    }

                    var s = t.prototype;
                    return s.show = function () {
                        var t = this;
                        if (!(this._element.parentNode && this._element.parentNode.nodeType === Node.ELEMENT_NODE && e(this._element).hasClass(i.ACTIVE) || e(this._element).hasClass(i.DISABLED))) {
                            var o, s, l = e(this._element).closest(r.NAV_LIST_GROUP)[0],
                                c = a.getSelectorFromElement(this._element);
                            if (l) {
                                var u = "UL" === l.nodeName ? r.ACTIVE_UL : r.ACTIVE;
                                s = (s = e.makeArray(e(l).find(u)))[s.length - 1]
                            }
                            var d = e.Event(n.HIDE, {relatedTarget: this._element}),
                                p = e.Event(n.SHOW, {relatedTarget: s});
                            if (s && e(s).trigger(d), e(this._element).trigger(p), !p.isDefaultPrevented() && !d.isDefaultPrevented()) {
                                c && (o = document.querySelector(c)), this._activate(this._element, l);
                                var f = function () {
                                    var i = e.Event(n.HIDDEN, {relatedTarget: t._element}),
                                        o = e.Event(n.SHOWN, {relatedTarget: s});
                                    e(s).trigger(i), e(t._element).trigger(o)
                                };
                                o ? this._activate(o, o.parentNode, f) : f()
                            }
                        }
                    }, s.dispose = function () {
                        e.removeData(this._element, "bs.tab"), this._element = null
                    }, s._activate = function (t, n, o) {
                        var s = this, l = ("UL" === n.nodeName ? e(n).find(r.ACTIVE_UL) : e(n).children(r.ACTIVE))[0],
                            c = o && l && e(l).hasClass(i.FADE), u = function () {
                                return s._transitionComplete(t, l, o)
                            };
                        if (l && c) {
                            var d = a.getTransitionDurationFromElement(l);
                            e(l).one(a.TRANSITION_END, u).emulateTransitionEnd(d)
                        } else u()
                    }, s._transitionComplete = function (t, n, o) {
                        if (n) {
                            e(n).removeClass(i.SHOW + " " + i.ACTIVE);
                            var s = e(n.parentNode).find(r.DROPDOWN_ACTIVE_CHILD)[0];
                            s && e(s).removeClass(i.ACTIVE), "tab" === n.getAttribute("role") && n.setAttribute("aria-selected", !1)
                        }
                        if (e(t).addClass(i.ACTIVE), "tab" === t.getAttribute("role") && t.setAttribute("aria-selected", !0), a.reflow(t), e(t).addClass(i.SHOW), t.parentNode && e(t.parentNode).hasClass(i.DROPDOWN_MENU)) {
                            var l = e(t).closest(r.DROPDOWN)[0];
                            if (l) {
                                var c = [].slice.call(l.querySelectorAll(r.DROPDOWN_TOGGLE));
                                e(c).addClass(i.ACTIVE)
                            }
                            t.setAttribute("aria-expanded", !0)
                        }
                        o && o()
                    }, t._jQueryInterface = function (n) {
                        return this.each(function () {
                            var i = e(this), o = i.data("bs.tab");
                            if (o || (o = new t(this), i.data("bs.tab", o)), "string" == typeof n) {
                                if (void 0 === o[n]) throw new TypeError('No method named "' + n + '"');
                                o[n]()
                            }
                        })
                    }, o(t, null, [{
                        key: "VERSION", get: function () {
                            return "4.1.2"
                        }
                    }]), t
                }();
            return e(document).on(n.CLICK_DATA_API, r.DATA_TOGGLE, function (t) {
                t.preventDefault(), s._jQueryInterface.call(e(this), "show")
            }), e.fn.tab = s._jQueryInterface, e.fn.tab.Constructor = s, e.fn.tab.noConflict = function () {
                return e.fn.tab = t, s._jQueryInterface
            }, s
        }(t);
        (function (e) {
            if (void 0 === e) throw new TypeError("Bootstrap's JavaScript requires jQuery. jQuery must be included before Bootstrap's JavaScript.");
            var t = e.fn.jquery.split(" ")[0].split(".");
            if (t[0] < 2 && t[1] < 9 || 1 === t[0] && 9 === t[1] && t[2] < 1 || t[0] >= 4) throw new Error("Bootstrap's JavaScript requires at least jQuery v1.9.1 but less than v4.0.0")
        })(t), e.Util = a, e.Alert = l, e.Button = c, e.Carousel = u, e.Collapse = d, e.Dropdown = p, e.Modal = f, e.Popover = m, e.Scrollspy = g, e.Tab = v, e.Tooltip = h, Object.defineProperty(e, "__esModule", {value: !0})
    }(t, n(3), n(7))
}, function (e, t, n) {
    "use strict";
    n.r(t), function (e) {
        for (
            /**!
             * @fileOverview Kickass library to create and place poppers near their reference elements.
             * @version 1.14.3
             * @license
             * Copyright (c) 2016 Federico Zivolo and contributors
             *
             * Permission is hereby granted, free of charge, to any person obtaining a copy
             * of this software and associated documentation files (the "Software"), to deal
             * in the Software without restriction, including without limitation the rights
             * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
             * copies of the Software, and to permit persons to whom the Software is
             * furnished to do so, subject to the following conditions:
             *
             * The above copyright notice and this permission notice shall be included in all
             * copies or substantial portions of the Software.
             *
             * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
             * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
             * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
             * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
             * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
             * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
             * SOFTWARE.
             */
            var n = "undefined" != typeof window && "undefined" != typeof document, i = ["Edge", "Trident", "Firefox"], o = 0, r = 0; r < i.length; r += 1) if (n && navigator.userAgent.indexOf(i[r]) >= 0) {
            o = 1;
            break
        }
        var s = n && window.Promise ? function (e) {
            var t = !1;
            return function () {
                t || (t = !0, window.Promise.resolve().then(function () {
                    t = !1, e()
                }))
            }
        } : function (e) {
            var t = !1;
            return function () {
                t || (t = !0, setTimeout(function () {
                    t = !1, e()
                }, o))
            }
        };

        function a(e) {
            return e && "[object Function]" === {}.toString.call(e)
        }

        function l(e, t) {
            if (1 !== e.nodeType) return [];
            var n = getComputedStyle(e, null);
            return t ? n[t] : n
        }

        function c(e) {
            return "HTML" === e.nodeName ? e : e.parentNode || e.host
        }

        function u(e) {
            if (!e) return document.body;
            switch (e.nodeName) {
                case"HTML":
                case"BODY":
                    return e.ownerDocument.body;
                case"#document":
                    return e.body
            }
            var t = l(e), n = t.overflow, i = t.overflowX, o = t.overflowY;
            return /(auto|scroll|overlay)/.test(n + o + i) ? e : u(c(e))
        }

        var d = n && !(!window.MSInputMethodContext || !document.documentMode),
            p = n && /MSIE 10/.test(navigator.userAgent);

        function f(e) {
            return 11 === e ? d : 10 === e ? p : d || p
        }

        function h(e) {
            if (!e) return document.documentElement;
            for (var t = f(10) ? document.body : null, n = e.offsetParent; n === t && e.nextElementSibling;) n = (e = e.nextElementSibling).offsetParent;
            var i = n && n.nodeName;
            return i && "BODY" !== i && "HTML" !== i ? -1 !== ["TD", "TABLE"].indexOf(n.nodeName) && "static" === l(n, "position") ? h(n) : n : e ? e.ownerDocument.documentElement : document.documentElement
        }

        function m(e) {
            return null !== e.parentNode ? m(e.parentNode) : e
        }

        function g(e, t) {
            if (!(e && e.nodeType && t && t.nodeType)) return document.documentElement;
            var n = e.compareDocumentPosition(t) & Node.DOCUMENT_POSITION_FOLLOWING, i = n ? e : t, o = n ? t : e,
                r = document.createRange();
            r.setStart(i, 0), r.setEnd(o, 0);
            var s = r.commonAncestorContainer;
            if (e !== s && t !== s || i.contains(o)) return function (e) {
                var t = e.nodeName;
                return "BODY" !== t && ("HTML" === t || h(e.firstElementChild) === e)
            }(s) ? s : h(s);
            var a = m(e);
            return a.host ? g(a.host, t) : g(e, m(t).host)
        }

        function v(e) {
            var t = "top" === (arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : "top") ? "scrollTop" : "scrollLeft",
                n = e.nodeName;
            if ("BODY" === n || "HTML" === n) {
                var i = e.ownerDocument.documentElement;
                return (e.ownerDocument.scrollingElement || i)[t]
            }
            return e[t]
        }

        function y(e, t) {
            var n = "x" === t ? "Left" : "Top", i = "Left" === n ? "Right" : "Bottom";
            return parseFloat(e["border" + n + "Width"], 10) + parseFloat(e["border" + i + "Width"], 10)
        }

        function b(e, t, n, i) {
            return Math.max(t["offset" + e], t["scroll" + e], n["client" + e], n["offset" + e], n["scroll" + e], f(10) ? n["offset" + e] + i["margin" + ("Height" === e ? "Top" : "Left")] + i["margin" + ("Height" === e ? "Bottom" : "Right")] : 0)
        }

        function w() {
            var e = document.body, t = document.documentElement, n = f(10) && getComputedStyle(t);
            return {height: b("Height", e, t, n), width: b("Width", e, t, n)}
        }

        var x = function (e, t) {
            if (!(e instanceof t)) throw new TypeError("Cannot call a class as a function")
        }, T = function () {
            function e(e, t) {
                for (var n = 0; n < t.length; n++) {
                    var i = t[n];
                    i.enumerable = i.enumerable || !1, i.configurable = !0, "value" in i && (i.writable = !0), Object.defineProperty(e, i.key, i)
                }
            }

            return function (t, n, i) {
                return n && e(t.prototype, n), i && e(t, i), t
            }
        }(), S = function (e, t, n) {
            return t in e ? Object.defineProperty(e, t, {
                value: n,
                enumerable: !0,
                configurable: !0,
                writable: !0
            }) : e[t] = n, e
        }, C = Object.assign || function (e) {
            for (var t = 1; t < arguments.length; t++) {
                var n = arguments[t];
                for (var i in n) Object.prototype.hasOwnProperty.call(n, i) && (e[i] = n[i])
            }
            return e
        };

        function E(e) {
            return C({}, e, {right: e.left + e.width, bottom: e.top + e.height})
        }

        function k(e) {
            var t = {};
            try {
                if (f(10)) {
                    t = e.getBoundingClientRect();
                    var n = v(e, "top"), i = v(e, "left");
                    t.top += n, t.left += i, t.bottom += n, t.right += i
                } else t = e.getBoundingClientRect()
            } catch (e) {
            }
            var o = {left: t.left, top: t.top, width: t.right - t.left, height: t.bottom - t.top},
                r = "HTML" === e.nodeName ? w() : {}, s = r.width || e.clientWidth || o.right - o.left,
                a = r.height || e.clientHeight || o.bottom - o.top, c = e.offsetWidth - s, u = e.offsetHeight - a;
            if (c || u) {
                var d = l(e);
                c -= y(d, "x"), u -= y(d, "y"), o.width -= c, o.height -= u
            }
            return E(o)
        }

        function _(e, t) {
            var n = arguments.length > 2 && void 0 !== arguments[2] && arguments[2], i = f(10),
                o = "HTML" === t.nodeName, r = k(e), s = k(t), a = u(e), c = l(t), d = parseFloat(c.borderTopWidth, 10),
                p = parseFloat(c.borderLeftWidth, 10);
            n && "HTML" === t.nodeName && (s.top = Math.max(s.top, 0), s.left = Math.max(s.left, 0));
            var h = E({top: r.top - s.top - d, left: r.left - s.left - p, width: r.width, height: r.height});
            if (h.marginTop = 0, h.marginLeft = 0, !i && o) {
                var m = parseFloat(c.marginTop, 10), g = parseFloat(c.marginLeft, 10);
                h.top -= d - m, h.bottom -= d - m, h.left -= p - g, h.right -= p - g, h.marginTop = m, h.marginLeft = g
            }
            return (i && !n ? t.contains(a) : t === a && "BODY" !== a.nodeName) && (h = function (e, t) {
                var n = arguments.length > 2 && void 0 !== arguments[2] && arguments[2], i = v(t, "top"),
                    o = v(t, "left"), r = n ? -1 : 1;
                return e.top += i * r, e.bottom += i * r, e.left += o * r, e.right += o * r, e
            }(h, t)), h
        }

        function A(e) {
            if (!e || !e.parentElement || f()) return document.documentElement;
            for (var t = e.parentElement; t && "none" === l(t, "transform");) t = t.parentElement;
            return t || document.documentElement
        }

        function I(e, t, n, i) {
            var o = arguments.length > 4 && void 0 !== arguments[4] && arguments[4], r = {top: 0, left: 0},
                s = o ? A(e) : g(e, t);
            if ("viewport" === i) r = function (e) {
                var t = arguments.length > 1 && void 0 !== arguments[1] && arguments[1],
                    n = e.ownerDocument.documentElement, i = _(e, n),
                    o = Math.max(n.clientWidth, window.innerWidth || 0),
                    r = Math.max(n.clientHeight, window.innerHeight || 0), s = t ? 0 : v(n), a = t ? 0 : v(n, "left");
                return E({top: s - i.top + i.marginTop, left: a - i.left + i.marginLeft, width: o, height: r})
            }(s, o); else {
                var a = void 0;
                "scrollParent" === i ? "BODY" === (a = u(c(t))).nodeName && (a = e.ownerDocument.documentElement) : a = "window" === i ? e.ownerDocument.documentElement : i;
                var d = _(a, s, o);
                if ("HTML" !== a.nodeName || function e(t) {
                    var n = t.nodeName;
                    return "BODY" !== n && "HTML" !== n && ("fixed" === l(t, "position") || e(c(t)))
                }(s)) r = d; else {
                    var p = w(), f = p.height, h = p.width;
                    r.top += d.top - d.marginTop, r.bottom = f + d.top, r.left += d.left - d.marginLeft, r.right = h + d.left
                }
            }
            return r.left += n, r.top += n, r.right -= n, r.bottom -= n, r
        }

        function O(e, t, n, i, o) {
            var r = arguments.length > 5 && void 0 !== arguments[5] ? arguments[5] : 0;
            if (-1 === e.indexOf("auto")) return e;
            var s = I(n, i, r, o), a = {
                top: {width: s.width, height: t.top - s.top},
                right: {width: s.right - t.right, height: s.height},
                bottom: {width: s.width, height: s.bottom - t.bottom},
                left: {width: t.left - s.left, height: s.height}
            }, l = Object.keys(a).map(function (e) {
                return C({key: e}, a[e], {
                    area: function (e) {
                        return e.width * e.height
                    }(a[e])
                })
            }).sort(function (e, t) {
                return t.area - e.area
            }), c = l.filter(function (e) {
                var t = e.width, i = e.height;
                return t >= n.clientWidth && i >= n.clientHeight
            }), u = c.length > 0 ? c[0].key : l[0].key, d = e.split("-")[1];
            return u + (d ? "-" + d : "")
        }

        function D(e, t, n) {
            var i = arguments.length > 3 && void 0 !== arguments[3] ? arguments[3] : null;
            return _(n, i ? A(t) : g(t, n), i)
        }

        function L(e) {
            var t = getComputedStyle(e), n = parseFloat(t.marginTop) + parseFloat(t.marginBottom),
                i = parseFloat(t.marginLeft) + parseFloat(t.marginRight);
            return {width: e.offsetWidth + i, height: e.offsetHeight + n}
        }

        function N(e) {
            var t = {left: "right", right: "left", bottom: "top", top: "bottom"};
            return e.replace(/left|right|bottom|top/g, function (e) {
                return t[e]
            })
        }

        function P(e, t, n) {
            n = n.split("-")[0];
            var i = L(e), o = {width: i.width, height: i.height}, r = -1 !== ["right", "left"].indexOf(n),
                s = r ? "top" : "left", a = r ? "left" : "top", l = r ? "height" : "width", c = r ? "width" : "height";
            return o[s] = t[s] + t[l] / 2 - i[l] / 2, o[a] = n === a ? t[a] - i[c] : t[N(a)], o
        }

        function M(e, t) {
            return Array.prototype.find ? e.find(t) : e.filter(t)[0]
        }

        function j(e, t, n) {
            return (void 0 === n ? e : e.slice(0, function (e, t, n) {
                if (Array.prototype.findIndex) return e.findIndex(function (e) {
                    return e[t] === n
                });
                var i = M(e, function (e) {
                    return e[t] === n
                });
                return e.indexOf(i)
            }(e, "name", n))).forEach(function (e) {
                e.function && console.warn("`modifier.function` is deprecated, use `modifier.fn`!");
                var n = e.function || e.fn;
                e.enabled && a(n) && (t.offsets.popper = E(t.offsets.popper), t.offsets.reference = E(t.offsets.reference), t = n(t, e))
            }), t
        }

        function H(e, t) {
            return e.some(function (e) {
                var n = e.name;
                return e.enabled && n === t
            })
        }

        function $(e) {
            for (var t = [!1, "ms", "Webkit", "Moz", "O"], n = e.charAt(0).toUpperCase() + e.slice(1), i = 0; i < t.length; i++) {
                var o = t[i], r = o ? "" + o + n : e;
                if (void 0 !== document.body.style[r]) return r
            }
            return null
        }

        function F(e) {
            var t = e.ownerDocument;
            return t ? t.defaultView : window
        }

        function R(e, t, n, i) {
            n.updateBound = i, F(e).addEventListener("resize", n.updateBound, {passive: !0});
            var o = u(e);
            return function e(t, n, i, o) {
                var r = "BODY" === t.nodeName, s = r ? t.ownerDocument.defaultView : t;
                s.addEventListener(n, i, {passive: !0}), r || e(u(s.parentNode), n, i, o), o.push(s)
            }(o, "scroll", n.updateBound, n.scrollParents), n.scrollElement = o, n.eventsEnabled = !0, n
        }

        function W() {
            this.state.eventsEnabled && (cancelAnimationFrame(this.scheduleUpdate), this.state = function (e, t) {
                return F(e).removeEventListener("resize", t.updateBound), t.scrollParents.forEach(function (e) {
                    e.removeEventListener("scroll", t.updateBound)
                }), t.updateBound = null, t.scrollParents = [], t.scrollElement = null, t.eventsEnabled = !1, t
            }(this.reference, this.state))
        }

        function V(e) {
            return "" !== e && !isNaN(parseFloat(e)) && isFinite(e)
        }

        function z(e, t) {
            Object.keys(t).forEach(function (n) {
                var i = "";
                -1 !== ["width", "height", "top", "right", "bottom", "left"].indexOf(n) && V(t[n]) && (i = "px"), e.style[n] = t[n] + i
            })
        }

        function q(e, t, n) {
            var i = M(e, function (e) {
                return e.name === t
            }), o = !!i && e.some(function (e) {
                return e.name === n && e.enabled && e.order < i.order
            });
            if (!o) {
                var r = "`" + t + "`", s = "`" + n + "`";
                console.warn(s + " modifier is required by " + r + " modifier in order to work, be sure to include it before " + r + "!")
            }
            return o
        }

        var B = ["auto-start", "auto", "auto-end", "top-start", "top", "top-end", "right-start", "right", "right-end", "bottom-end", "bottom", "bottom-start", "left-end", "left", "left-start"],
            U = B.slice(3);

        function G(e) {
            var t = arguments.length > 1 && void 0 !== arguments[1] && arguments[1], n = U.indexOf(e),
                i = U.slice(n + 1).concat(U.slice(0, n));
            return t ? i.reverse() : i
        }

        var Y = {FLIP: "flip", CLOCKWISE: "clockwise", COUNTERCLOCKWISE: "counterclockwise"};

        function K(e, t, n, i) {
            var o = [0, 0], r = -1 !== ["right", "left"].indexOf(i), s = e.split(/(\+|\-)/).map(function (e) {
                return e.trim()
            }), a = s.indexOf(M(s, function (e) {
                return -1 !== e.search(/,|\s/)
            }));
            s[a] && -1 === s[a].indexOf(",") && console.warn("Offsets separated by white space(s) are deprecated, use a comma (,) instead.");
            var l = /\s*,\s*|\s+/,
                c = -1 !== a ? [s.slice(0, a).concat([s[a].split(l)[0]]), [s[a].split(l)[1]].concat(s.slice(a + 1))] : [s];
            return (c = c.map(function (e, i) {
                var o = (1 === i ? !r : r) ? "height" : "width", s = !1;
                return e.reduce(function (e, t) {
                    return "" === e[e.length - 1] && -1 !== ["+", "-"].indexOf(t) ? (e[e.length - 1] = t, s = !0, e) : s ? (e[e.length - 1] += t, s = !1, e) : e.concat(t)
                }, []).map(function (e) {
                    return function (e, t, n, i) {
                        var o = e.match(/((?:\-|\+)?\d*\.?\d*)(.*)/), r = +o[1], s = o[2];
                        if (!r) return e;
                        if (0 === s.indexOf("%")) {
                            var a = void 0;
                            switch (s) {
                                case"%p":
                                    a = n;
                                    break;
                                case"%":
                                case"%r":
                                default:
                                    a = i
                            }
                            return E(a)[t] / 100 * r
                        }
                        if ("vh" === s || "vw" === s) return ("vh" === s ? Math.max(document.documentElement.clientHeight, window.innerHeight || 0) : Math.max(document.documentElement.clientWidth, window.innerWidth || 0)) / 100 * r;
                        return r
                    }(e, o, t, n)
                })
            })).forEach(function (e, t) {
                e.forEach(function (n, i) {
                    V(n) && (o[t] += n * ("-" === e[i - 1] ? -1 : 1))
                })
            }), o
        }

        var Q = {
            placement: "bottom", positionFixed: !1, eventsEnabled: !0, removeOnDestroy: !1, onCreate: function () {
            }, onUpdate: function () {
            }, modifiers: {
                shift: {
                    order: 100, enabled: !0, fn: function (e) {
                        var t = e.placement, n = t.split("-")[0], i = t.split("-")[1];
                        if (i) {
                            var o = e.offsets, r = o.reference, s = o.popper, a = -1 !== ["bottom", "top"].indexOf(n),
                                l = a ? "left" : "top", c = a ? "width" : "height",
                                u = {start: S({}, l, r[l]), end: S({}, l, r[l] + r[c] - s[c])};
                            e.offsets.popper = C({}, s, u[i])
                        }
                        return e
                    }
                }, offset: {
                    order: 200, enabled: !0, fn: function (e, t) {
                        var n = t.offset, i = e.placement, o = e.offsets, r = o.popper, s = o.reference,
                            a = i.split("-")[0], l = void 0;
                        return l = V(+n) ? [+n, 0] : K(n, r, s, a), "left" === a ? (r.top += l[0], r.left -= l[1]) : "right" === a ? (r.top += l[0], r.left += l[1]) : "top" === a ? (r.left += l[0], r.top -= l[1]) : "bottom" === a && (r.left += l[0], r.top += l[1]), e.popper = r, e
                    }, offset: 0
                }, preventOverflow: {
                    order: 300, enabled: !0, fn: function (e, t) {
                        var n = t.boundariesElement || h(e.instance.popper);
                        e.instance.reference === n && (n = h(n));
                        var i = $("transform"), o = e.instance.popper.style, r = o.top, s = o.left, a = o[i];
                        o.top = "", o.left = "", o[i] = "";
                        var l = I(e.instance.popper, e.instance.reference, t.padding, n, e.positionFixed);
                        o.top = r, o.left = s, o[i] = a, t.boundaries = l;
                        var c = t.priority, u = e.offsets.popper, d = {
                            primary: function (e) {
                                var n = u[e];
                                return u[e] < l[e] && !t.escapeWithReference && (n = Math.max(u[e], l[e])), S({}, e, n)
                            }, secondary: function (e) {
                                var n = "right" === e ? "left" : "top", i = u[n];
                                return u[e] > l[e] && !t.escapeWithReference && (i = Math.min(u[n], l[e] - ("right" === e ? u.width : u.height))), S({}, n, i)
                            }
                        };
                        return c.forEach(function (e) {
                            var t = -1 !== ["left", "top"].indexOf(e) ? "primary" : "secondary";
                            u = C({}, u, d[t](e))
                        }), e.offsets.popper = u, e
                    }, priority: ["left", "right", "top", "bottom"], padding: 5, boundariesElement: "scrollParent"
                }, keepTogether: {
                    order: 400, enabled: !0, fn: function (e) {
                        var t = e.offsets, n = t.popper, i = t.reference, o = e.placement.split("-")[0], r = Math.floor,
                            s = -1 !== ["top", "bottom"].indexOf(o), a = s ? "right" : "bottom", l = s ? "left" : "top",
                            c = s ? "width" : "height";
                        return n[a] < r(i[l]) && (e.offsets.popper[l] = r(i[l]) - n[c]), n[l] > r(i[a]) && (e.offsets.popper[l] = r(i[a])), e
                    }
                }, arrow: {
                    order: 500, enabled: !0, fn: function (e, t) {
                        var n;
                        if (!q(e.instance.modifiers, "arrow", "keepTogether")) return e;
                        var i = t.element;
                        if ("string" == typeof i) {
                            if (!(i = e.instance.popper.querySelector(i))) return e
                        } else if (!e.instance.popper.contains(i)) return console.warn("WARNING: `arrow.element` must be child of its popper element!"), e;
                        var o = e.placement.split("-")[0], r = e.offsets, s = r.popper, a = r.reference,
                            c = -1 !== ["left", "right"].indexOf(o), u = c ? "height" : "width", d = c ? "Top" : "Left",
                            p = d.toLowerCase(), f = c ? "left" : "top", h = c ? "bottom" : "right", m = L(i)[u];
                        a[h] - m < s[p] && (e.offsets.popper[p] -= s[p] - (a[h] - m)), a[p] + m > s[h] && (e.offsets.popper[p] += a[p] + m - s[h]), e.offsets.popper = E(e.offsets.popper);
                        var g = a[p] + a[u] / 2 - m / 2, v = l(e.instance.popper), y = parseFloat(v["margin" + d], 10),
                            b = parseFloat(v["border" + d + "Width"], 10), w = g - e.offsets.popper[p] - y - b;
                        return w = Math.max(Math.min(s[u] - m, w), 0), e.arrowElement = i, e.offsets.arrow = (S(n = {}, p, Math.round(w)), S(n, f, ""), n), e
                    }, element: "[x-arrow]"
                }, flip: {
                    order: 600, enabled: !0, fn: function (e, t) {
                        if (H(e.instance.modifiers, "inner")) return e;
                        if (e.flipped && e.placement === e.originalPlacement) return e;
                        var n = I(e.instance.popper, e.instance.reference, t.padding, t.boundariesElement, e.positionFixed),
                            i = e.placement.split("-")[0], o = N(i), r = e.placement.split("-")[1] || "", s = [];
                        switch (t.behavior) {
                            case Y.FLIP:
                                s = [i, o];
                                break;
                            case Y.CLOCKWISE:
                                s = G(i);
                                break;
                            case Y.COUNTERCLOCKWISE:
                                s = G(i, !0);
                                break;
                            default:
                                s = t.behavior
                        }
                        return s.forEach(function (a, l) {
                            if (i !== a || s.length === l + 1) return e;
                            i = e.placement.split("-")[0], o = N(i);
                            var c = e.offsets.popper, u = e.offsets.reference, d = Math.floor,
                                p = "left" === i && d(c.right) > d(u.left) || "right" === i && d(c.left) < d(u.right) || "top" === i && d(c.bottom) > d(u.top) || "bottom" === i && d(c.top) < d(u.bottom),
                                f = d(c.left) < d(n.left), h = d(c.right) > d(n.right), m = d(c.top) < d(n.top),
                                g = d(c.bottom) > d(n.bottom),
                                v = "left" === i && f || "right" === i && h || "top" === i && m || "bottom" === i && g,
                                y = -1 !== ["top", "bottom"].indexOf(i),
                                b = !!t.flipVariations && (y && "start" === r && f || y && "end" === r && h || !y && "start" === r && m || !y && "end" === r && g);
                            (p || v || b) && (e.flipped = !0, (p || v) && (i = s[l + 1]), b && (r = function (e) {
                                return "end" === e ? "start" : "start" === e ? "end" : e
                            }(r)), e.placement = i + (r ? "-" + r : ""), e.offsets.popper = C({}, e.offsets.popper, P(e.instance.popper, e.offsets.reference, e.placement)), e = j(e.instance.modifiers, e, "flip"))
                        }), e
                    }, behavior: "flip", padding: 5, boundariesElement: "viewport"
                }, inner: {
                    order: 700, enabled: !1, fn: function (e) {
                        var t = e.placement, n = t.split("-")[0], i = e.offsets, o = i.popper, r = i.reference,
                            s = -1 !== ["left", "right"].indexOf(n), a = -1 === ["top", "left"].indexOf(n);
                        return o[s ? "left" : "top"] = r[n] - (a ? o[s ? "width" : "height"] : 0), e.placement = N(t), e.offsets.popper = E(o), e
                    }
                }, hide: {
                    order: 800, enabled: !0, fn: function (e) {
                        if (!q(e.instance.modifiers, "hide", "preventOverflow")) return e;
                        var t = e.offsets.reference, n = M(e.instance.modifiers, function (e) {
                            return "preventOverflow" === e.name
                        }).boundaries;
                        if (t.bottom < n.top || t.left > n.right || t.top > n.bottom || t.right < n.left) {
                            if (!0 === e.hide) return e;
                            e.hide = !0, e.attributes["x-out-of-boundaries"] = ""
                        } else {
                            if (!1 === e.hide) return e;
                            e.hide = !1, e.attributes["x-out-of-boundaries"] = !1
                        }
                        return e
                    }
                }, computeStyle: {
                    order: 850, enabled: !0, fn: function (e, t) {
                        var n = t.x, i = t.y, o = e.offsets.popper, r = M(e.instance.modifiers, function (e) {
                            return "applyStyle" === e.name
                        }).gpuAcceleration;
                        void 0 !== r && console.warn("WARNING: `gpuAcceleration` option moved to `computeStyle` modifier and will not be supported in future versions of Popper.js!");
                        var s = void 0 !== r ? r : t.gpuAcceleration, a = k(h(e.instance.popper)),
                            l = {position: o.position}, c = {
                                left: Math.floor(o.left),
                                top: Math.round(o.top),
                                bottom: Math.round(o.bottom),
                                right: Math.floor(o.right)
                            }, u = "bottom" === n ? "top" : "bottom", d = "right" === i ? "left" : "right",
                            p = $("transform"), f = void 0, m = void 0;
                        if (m = "bottom" === u ? -a.height + c.bottom : c.top, f = "right" === d ? -a.width + c.right : c.left, s && p) l[p] = "translate3d(" + f + "px, " + m + "px, 0)", l[u] = 0, l[d] = 0, l.willChange = "transform"; else {
                            var g = "bottom" === u ? -1 : 1, v = "right" === d ? -1 : 1;
                            l[u] = m * g, l[d] = f * v, l.willChange = u + ", " + d
                        }
                        var y = {"x-placement": e.placement};
                        return e.attributes = C({}, y, e.attributes), e.styles = C({}, l, e.styles), e.arrowStyles = C({}, e.offsets.arrow, e.arrowStyles), e
                    }, gpuAcceleration: !0, x: "bottom", y: "right"
                }, applyStyle: {
                    order: 900, enabled: !0, fn: function (e) {
                        return z(e.instance.popper, e.styles), function (e, t) {
                            Object.keys(t).forEach(function (n) {
                                !1 !== t[n] ? e.setAttribute(n, t[n]) : e.removeAttribute(n)
                            })
                        }(e.instance.popper, e.attributes), e.arrowElement && Object.keys(e.arrowStyles).length && z(e.arrowElement, e.arrowStyles), e
                    }, onLoad: function (e, t, n, i, o) {
                        var r = D(o, t, e, n.positionFixed),
                            s = O(n.placement, r, t, e, n.modifiers.flip.boundariesElement, n.modifiers.flip.padding);
                        return t.setAttribute("x-placement", s), z(t, {position: n.positionFixed ? "fixed" : "absolute"}), n
                    }, gpuAcceleration: void 0
                }
            }
        }, Z = function () {
            function e(t, n) {
                var i = this, o = arguments.length > 2 && void 0 !== arguments[2] ? arguments[2] : {};
                x(this, e), this.scheduleUpdate = function () {
                    return requestAnimationFrame(i.update)
                }, this.update = s(this.update.bind(this)), this.options = C({}, e.Defaults, o), this.state = {
                    isDestroyed: !1,
                    isCreated: !1,
                    scrollParents: []
                }, this.reference = t && t.jquery ? t[0] : t, this.popper = n && n.jquery ? n[0] : n, this.options.modifiers = {}, Object.keys(C({}, e.Defaults.modifiers, o.modifiers)).forEach(function (t) {
                    i.options.modifiers[t] = C({}, e.Defaults.modifiers[t] || {}, o.modifiers ? o.modifiers[t] : {})
                }), this.modifiers = Object.keys(this.options.modifiers).map(function (e) {
                    return C({name: e}, i.options.modifiers[e])
                }).sort(function (e, t) {
                    return e.order - t.order
                }), this.modifiers.forEach(function (e) {
                    e.enabled && a(e.onLoad) && e.onLoad(i.reference, i.popper, i.options, e, i.state)
                }), this.update();
                var r = this.options.eventsEnabled;
                r && this.enableEventListeners(), this.state.eventsEnabled = r
            }

            return T(e, [{
                key: "update", value: function () {
                    return function () {
                        if (!this.state.isDestroyed) {
                            var e = {
                                instance: this,
                                styles: {},
                                arrowStyles: {},
                                attributes: {},
                                flipped: !1,
                                offsets: {}
                            };
                            e.offsets.reference = D(this.state, this.popper, this.reference, this.options.positionFixed), e.placement = O(this.options.placement, e.offsets.reference, this.popper, this.reference, this.options.modifiers.flip.boundariesElement, this.options.modifiers.flip.padding), e.originalPlacement = e.placement, e.positionFixed = this.options.positionFixed, e.offsets.popper = P(this.popper, e.offsets.reference, e.placement), e.offsets.popper.position = this.options.positionFixed ? "fixed" : "absolute", e = j(this.modifiers, e), this.state.isCreated ? this.options.onUpdate(e) : (this.state.isCreated = !0, this.options.onCreate(e))
                        }
                    }.call(this)
                }
            }, {
                key: "destroy", value: function () {
                    return function () {
                        return this.state.isDestroyed = !0, H(this.modifiers, "applyStyle") && (this.popper.removeAttribute("x-placement"), this.popper.style.position = "", this.popper.style.top = "", this.popper.style.left = "", this.popper.style.right = "", this.popper.style.bottom = "", this.popper.style.willChange = "", this.popper.style[$("transform")] = ""), this.disableEventListeners(), this.options.removeOnDestroy && this.popper.parentNode.removeChild(this.popper), this
                    }.call(this)
                }
            }, {
                key: "enableEventListeners", value: function () {
                    return function () {
                        this.state.eventsEnabled || (this.state = R(this.reference, this.options, this.state, this.scheduleUpdate))
                    }.call(this)
                }
            }, {
                key: "disableEventListeners", value: function () {
                    return W.call(this)
                }
            }]), e
        }();
        Z.Utils = ("undefined" != typeof window ? window : e).PopperUtils, Z.placements = B, Z.Defaults = Q, t.default = Z
    }.call(this, n(5))
}, function (e, t, n) {
    var i;
    !function () {
        var o, r, s, a = {
                frameRate: 150,
                animationTime: 400,
                stepSize: 100,
                pulseAlgorithm: !0,
                pulseScale: 4,
                pulseNormalize: 1,
                accelerationDelta: 50,
                accelerationMax: 3,
                keyboardSupport: !0,
                arrowScroll: 50,
                fixedBackground: !0,
                excluded: ""
            }, l = a, c = !1, u = !1, d = {x: 0, y: 0}, p = !1, f = document.documentElement, h = [],
            m = /^Mac/.test(navigator.platform),
            g = {left: 37, up: 38, right: 39, down: 40, spacebar: 32, pageup: 33, pagedown: 34, end: 35, home: 36},
            v = {37: 1, 38: 1, 39: 1, 40: 1};

        function y() {
            if (!p && document.body) {
                p = !0;
                var e = document.body, t = document.documentElement, n = window.innerHeight, i = e.scrollHeight;
                if (f = document.compatMode.indexOf("CSS") >= 0 ? t : e, o = e, l.keyboardSupport && j("keydown", C), top != self) u = !0; else if (J && i > n && (e.offsetHeight <= n || t.offsetHeight <= n)) {
                    var a, d = document.createElement("div");
                    d.style.cssText = "position:absolute; z-index:-10000; top:0; left:0; right:0; height:" + f.scrollHeight + "px", document.body.appendChild(d), s = function () {
                        a || (a = setTimeout(function () {
                            c || (d.style.height = "0", d.style.height = f.scrollHeight + "px", a = null)
                        }, 500))
                    }, setTimeout(s, 10), j("resize", s);
                    if ((r = new V(s)).observe(e, {
                        attributes: !0,
                        childList: !0,
                        characterData: !1
                    }), f.offsetHeight <= n) {
                        var h = document.createElement("div");
                        h.style.clear = "both", e.appendChild(h)
                    }
                }
                l.fixedBackground || c || (e.style.backgroundAttachment = "scroll", t.style.backgroundAttachment = "scroll")
            }
        }

        var b = [], w = !1, x = Date.now();

        function T(e, t, n) {
            if (function (e, t) {
                e = e > 0 ? 1 : -1, t = t > 0 ? 1 : -1, (d.x !== e || d.y !== t) && (d.x = e, d.y = t, b = [], x = 0)
            }(t, n), 1 != l.accelerationMax) {
                var i = Date.now() - x;
                if (i < l.accelerationDelta) {
                    var o = (1 + 50 / i) / 2;
                    o > 1 && (o = Math.min(o, l.accelerationMax), t *= o, n *= o)
                }
                x = Date.now()
            }
            if (b.push({x: t, y: n, lastX: t < 0 ? .99 : -.99, lastY: n < 0 ? .99 : -.99, start: Date.now()}), !w) {
                var r = e === document.body, s = function (i) {
                    for (var o = Date.now(), a = 0, c = 0, u = 0; u < b.length; u++) {
                        var d = b[u], p = o - d.start, f = p >= l.animationTime, h = f ? 1 : p / l.animationTime;
                        l.pulseAlgorithm && (h = B(h));
                        var m = d.x * h - d.lastX >> 0, g = d.y * h - d.lastY >> 0;
                        a += m, c += g, d.lastX += m, d.lastY += g, f && (b.splice(u, 1), u--)
                    }
                    r ? window.scrollBy(a, c) : (a && (e.scrollLeft += a), c && (e.scrollTop += c)), t || n || (b = []), b.length ? W(s, e, 1e3 / l.frameRate + 1) : w = !1
                };
                W(s, e, 0), w = !0
            }
        }

        function S(e) {
            p || y();
            var t = e.target;
            if (e.defaultPrevented || e.ctrlKey) return !0;
            if ($(o, "embed") || $(t, "embed") && /\.pdf/i.test(t.src) || $(o, "object") || t.shadowRoot) return !0;
            var n = -e.wheelDeltaX || e.deltaX || 0, i = -e.wheelDeltaY || e.deltaY || 0;
            m && (e.wheelDeltaX && F(e.wheelDeltaX, 120) && (n = e.wheelDeltaX / Math.abs(e.wheelDeltaX) * -120), e.wheelDeltaY && F(e.wheelDeltaY, 120) && (i = e.wheelDeltaY / Math.abs(e.wheelDeltaY) * -120)), n || i || (i = -e.wheelDelta || 0), 1 === e.deltaMode && (n *= 40, i *= 40);
            var r = L(t);
            return r ? !!function (e) {
                if (!e) return;
                h.length || (h = [e, e, e]);
                return e = Math.abs(e), h.push(e), h.shift(), clearTimeout(_), _ = setTimeout(function () {
                    try {
                        localStorage.SS_deltaBuffer = h.join(",")
                    } catch (e) {
                    }
                }, 1e3), !R(120) && !R(100)
            }(i) || (Math.abs(n) > 1.2 && (n *= l.stepSize / 120), Math.abs(i) > 1.2 && (i *= l.stepSize / 120), T(r, n, i), e.preventDefault(), void O()) : !u || !K || (Object.defineProperty(e, "target", {value: window.frameElement}), parent.wheel(e))
        }

        function C(e) {
            var t = e.target, n = e.ctrlKey || e.altKey || e.metaKey || e.shiftKey && e.keyCode !== g.spacebar;
            document.body.contains(o) || (o = document.activeElement);
            var i = /^(button|submit|radio|checkbox|file|color|image)$/i;
            if (e.defaultPrevented || /^(textarea|select|embed|object)$/i.test(t.nodeName) || $(t, "input") && !i.test(t.type) || $(o, "video") || function (e) {
                var t = e.target, n = !1;
                if (-1 != document.URL.indexOf("www.youtube.com/watch")) do {
                    if (n = t.classList && t.classList.contains("html5-video-controls")) break
                } while (t = t.parentNode);
                return n
            }(e) || t.isContentEditable || n) return !0;
            if (($(t, "button") || $(t, "input") && i.test(t.type)) && e.keyCode === g.spacebar) return !0;
            if ($(t, "input") && "radio" == t.type && v[e.keyCode]) return !0;
            var r = 0, s = 0, a = L(o);
            if (!a) return !u || !K || parent.keydown(e);
            var c = a.clientHeight;
            switch (a == document.body && (c = window.innerHeight), e.keyCode) {
                case g.up:
                    s = -l.arrowScroll;
                    break;
                case g.down:
                    s = l.arrowScroll;
                    break;
                case g.spacebar:
                    s = -(e.shiftKey ? 1 : -1) * c * .9;
                    break;
                case g.pageup:
                    s = .9 * -c;
                    break;
                case g.pagedown:
                    s = .9 * c;
                    break;
                case g.home:
                    s = -a.scrollTop;
                    break;
                case g.end:
                    var d = a.scrollHeight - a.scrollTop - c;
                    s = d > 0 ? d + 10 : 0;
                    break;
                case g.left:
                    r = -l.arrowScroll;
                    break;
                case g.right:
                    r = l.arrowScroll;
                    break;
                default:
                    return !0
            }
            T(a, r, s), e.preventDefault(), O()
        }

        function E(e) {
            o = e.target
        }

        var k, _, A = function () {
            var e = 0;
            return function (t) {
                return t.uniqueID || (t.uniqueID = e++)
            }
        }(), I = {};

        function O() {
            clearTimeout(k), k = setInterval(function () {
                I = {}
            }, 1e3)
        }

        function D(e, t) {
            for (var n = e.length; n--;) I[A(e[n])] = t;
            return t
        }

        function L(e) {
            var t = [], n = document.body, i = f.scrollHeight;
            do {
                var o = I[A(e)];
                if (o) return D(t, o);
                if (t.push(e), i === e.scrollHeight) {
                    var r = P(f) && P(n) || M(f);
                    if (u && N(f) || !u && r) return D(t, z())
                } else if (N(e) && M(e)) return D(t, e)
            } while (e = e.parentElement)
        }

        function N(e) {
            return e.clientHeight + 10 < e.scrollHeight
        }

        function P(e) {
            return "hidden" !== getComputedStyle(e, "").getPropertyValue("overflow-y")
        }

        function M(e) {
            var t = getComputedStyle(e, "").getPropertyValue("overflow-y");
            return "scroll" === t || "auto" === t
        }

        function j(e, t) {
            window.addEventListener(e, t, !1)
        }

        function H(e, t) {
            window.removeEventListener(e, t, !1)
        }

        function $(e, t) {
            return (e.nodeName || "").toLowerCase() === t.toLowerCase()
        }

        if (window.localStorage && localStorage.SS_deltaBuffer) try {
            h = localStorage.SS_deltaBuffer.split(",")
        } catch (e) {
        }

        function F(e, t) {
            return Math.floor(e / t) == e / t
        }

        function R(e) {
            return F(h[0], e) && F(h[1], e) && F(h[2], e)
        }

        var W = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || function (e, t, n) {
            window.setTimeout(e, n || 1e3 / 60)
        }, V = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver, z = function () {
            var e;
            return function () {
                if (!e) {
                    var t = document.createElement("div");
                    t.style.cssText = "height:10000px;width:1px;", document.body.appendChild(t);
                    var n = document.body.scrollTop;
                    document.documentElement.scrollTop;
                    window.scrollBy(0, 3), e = document.body.scrollTop != n ? document.body : document.documentElement, window.scrollBy(0, -3), document.body.removeChild(t)
                }
                return e
            }
        }();

        function q(e) {
            var t, n;
            return (e *= l.pulseScale) < 1 ? t = e - (1 - Math.exp(-e)) : (e -= 1, t = (n = Math.exp(-1)) + (1 - Math.exp(-e)) * (1 - n)), t * l.pulseNormalize
        }

        function B(e) {
            return e >= 1 ? 1 : e <= 0 ? 0 : (1 == l.pulseNormalize && (l.pulseNormalize /= q(1)), q(e))
        }

        var U, G = window.navigator.userAgent, Y = /Edge/.test(G), K = /chrome/i.test(G) && !Y,
            Q = /safari/i.test(G) && !Y, Z = /mobile/i.test(G), X = /Windows NT 6.1/i.test(G) && /rv:11/i.test(G),
            J = Q && (/Version\/8/i.test(G) || /Version\/9/i.test(G)), ee = (K || Q || X) && !Z;

        function te(e) {
            for (var t in e) a.hasOwnProperty(t) && (l[t] = e[t])
        }

        "onwheel" in document.createElement("div") ? U = "wheel" : "onmousewheel" in document.createElement("div") && (U = "mousewheel"), U && ee && (j(U, S), j("mousedown", E), j("load", y)), te.destroy = function () {
            r && r.disconnect(), H(U, S), H("mousedown", E), H("keydown", C), H("resize", s), H("load", y)
        }, window.SmoothScrollOptions && te(window.SmoothScrollOptions), void 0 === (i = function () {
            return te
        }.call(t, n, t, e)) || (e.exports = i)
    }()
}, function (e, t) {
    !function () {
        "use strict";
        if ("undefined" != typeof window) {
            var e = window.navigator.userAgent.match(/Edge\/(\d{2})\./), t = !!e && parseInt(e[1], 10) >= 16;
            if ("objectFit" in document.documentElement.style != 0 && !t) return void (window.objectFitPolyfill = function () {
                return !1
            });
            var n = function (e, t, n) {
                var i, o, r, s, a;
                if ((n = n.split(" ")).length < 2 && (n[1] = n[0]), "x" === e) i = n[0], o = n[1], r = "left", s = "right", a = t.clientWidth; else {
                    if ("y" !== e) return;
                    i = n[1], o = n[0], r = "top", s = "bottom", a = t.clientHeight
                }
                return i === r || o === r ? void (t.style[r] = "0") : i === s || o === s ? void (t.style[s] = "0") : "center" === i || "50%" === i ? (t.style[r] = "50%", void (t.style["margin-" + r] = a / -2 + "px")) : i.indexOf("%") >= 0 ? void ((i = parseInt(i)) < 50 ? (t.style[r] = i + "%", t.style["margin-" + r] = a * (i / -100) + "px") : (i = 100 - i, t.style[s] = i + "%", t.style["margin-" + s] = a * (i / -100) + "px")) : void (t.style[r] = i)
            }, i = function (e) {
                var t = e.dataset ? e.dataset.objectFit : e.getAttribute("data-object-fit"),
                    i = e.dataset ? e.dataset.objectPosition : e.getAttribute("data-object-position");
                t = t || "cover", i = i || "50% 50%";
                var o = e.parentNode;
                (function (e) {
                    var t = window.getComputedStyle(e, null), n = t.getPropertyValue("position"),
                        i = t.getPropertyValue("overflow"), o = t.getPropertyValue("display");
                    n && "static" !== n || (e.style.position = "relative"), "hidden" !== i && (e.style.overflow = "hidden"), o && "inline" !== o || (e.style.display = "block"), 0 === e.clientHeight && (e.style.height = "100%"), -1 === e.className.indexOf("object-fit-polyfill") && (e.className = e.className + " object-fit-polyfill")
                })(o), function (e) {
                    var t = window.getComputedStyle(e, null), n = {
                        "max-width": "none",
                        "max-height": "none",
                        "min-width": "0px",
                        "min-height": "0px",
                        top: "auto",
                        right: "auto",
                        bottom: "auto",
                        left: "auto",
                        "margin-top": "0px",
                        "margin-right": "0px",
                        "margin-bottom": "0px",
                        "margin-left": "0px"
                    };
                    for (var i in n) t.getPropertyValue(i) !== n[i] && (e.style[i] = n[i])
                }(e), e.style.position = "absolute", e.style.height = "100%", e.style.width = "auto", "scale-down" === t && (e.style.height = "auto", e.clientWidth < o.clientWidth && e.clientHeight < o.clientHeight ? (n("x", e, i), n("y", e, i)) : (t = "contain", e.style.height = "100%")), "none" === t ? (e.style.width = "auto", e.style.height = "auto", n("x", e, i), n("y", e, i)) : "cover" === t && e.clientWidth > o.clientWidth || "contain" === t && e.clientWidth < o.clientWidth ? (e.style.top = "0", e.style.marginTop = "0", n("x", e, i)) : "scale-down" !== t && (e.style.width = "100%", e.style.height = "auto", e.style.left = "0", e.style.marginLeft = "0", n("y", e, i))
            }, o = function (e) {
                if (void 0 === e) e = document.querySelectorAll("[data-object-fit]"); else if (e && e.nodeName) e = [e]; else {
                    if ("object" != typeof e || !e.length || !e[0].nodeName) return !1;
                    e = e
                }
                for (var n = 0; n < e.length; n++) if (e[n].nodeName) {
                    var o = e[n].nodeName.toLowerCase();
                    "img" !== o || t ? "video" === o && (e[n].readyState > 0 ? i(e[n]) : e[n].addEventListener("loadedmetadata", function () {
                        i(this)
                    })) : e[n].complete ? i(e[n]) : e[n].addEventListener("load", function () {
                        i(this)
                    })
                }
                return !0
            };
            document.addEventListener("DOMContentLoaded", function () {
                o()
            }), window.addEventListener("resize", function () {
                o()
            }), window.objectFitPolyfill = o
        }
    }()
}, function (e, t) {
    !function (e) {
        page.registerVendor("Jquery"), page.initJquery = function () {
            let t = document.head.querySelector('meta[name="csrf-token"]');
            t && e.ajaxSetup({headers: {"X-CSRF-TOKEN": t.content}})
        }
    }(jQuery), jQuery.fn.hasDataAttr = function (e) {
        return $(this)[0].hasAttribute("data-" + e)
    }, jQuery.fn.dataAttr = function (e, t) {
        return void 0 == $(this)[0] ? t : $(this)[0].getAttribute("data-" + e) || t
    }, jQuery.expr[":"].search = function (e, t, n) {
        return $(e).html().toUpperCase().indexOf(n[3].toUpperCase()) >= 0
    }, jQuery.fn.outerHTML = function () {
        var e = "";
        return this.each(function () {
            e += $(this).prop("outerHTML")
        }), e
    }, jQuery.fn.fullHTML = function () {
        var e = "";
        return $(this).each(function () {
            e += $(this).outerHTML()
        }), e
    }, jQuery.fn.scrollToEnd = function () {
        return $(this).scrollTop($(this).prop("scrollHeight")), this
    }
}, function (e, t) {
    !function (e) {
        page.registerVendor("Bootstrap"), page.initBootstrap = function () {
            e('[data-toggle="tooltip"]').tooltip(), e('[data-toggle="popover"]').popover(), e(document).on("click", ".custom-checkbox", function () {
                var t = e(this).children(".custom-control-input").not(":disabled");
                t.prop("checked", !t.prop("checked")).trigger("change")
            }), e(document).on("click", ".custom-radio", function () {
                e(this).children(".custom-control-input").not(":disabled").prop("checked", !0).trigger("change")
            })
        }
    }(jQuery)
}, function (e, t, n) {
    window.AOS = n(13), jQuery, page.registerVendor("AOS"), page.initAOS = function () {
        var e = {offset: 220, duration: 1500};
        page.defaults.disableAOSonMobile && (e.disable = "mobile"), AOS.init(e)
    }
}, function (e, t, n) {
    e.exports = function (e) {
        function t(i) {
            if (n[i]) return n[i].exports;
            var o = n[i] = {exports: {}, id: i, loaded: !1};
            return e[i].call(o.exports, o, o.exports, t), o.loaded = !0, o.exports
        }

        var n = {};
        return t.m = e, t.c = n, t.p = "dist/", t(0)
    }([function (e, t, n) {
        "use strict";

        function i(e) {
            return e && e.__esModule ? e : {default: e}
        }

        var o = Object.assign || function (e) {
                for (var t = 1; t < arguments.length; t++) {
                    var n = arguments[t];
                    for (var i in n) Object.prototype.hasOwnProperty.call(n, i) && (e[i] = n[i])
                }
                return e
            }, r = n(1), s = (i(r), n(6)), a = i(s), l = n(7), c = i(l), u = n(8), d = i(u), p = n(9), f = i(p), h = n(10),
            m = i(h), g = n(11), v = i(g), y = n(14), b = i(y), w = [], x = !1, T = {
                offset: 120,
                delay: 0,
                easing: "ease",
                duration: 400,
                disable: !1,
                once: !1,
                startEvent: "DOMContentLoaded",
                throttleDelay: 99,
                debounceDelay: 50,
                disableMutationObserver: !1
            }, S = function () {
                var e = arguments.length > 0 && void 0 !== arguments[0] && arguments[0];
                if (e && (x = !0), x) return w = (0, v.default)(w, T), (0, m.default)(w, T.once), w
            }, C = function () {
                w = (0, b.default)(), S()
            };
        e.exports = {
            init: function (e) {
                T = o(T, e), w = (0, b.default)();
                var t = document.all && !window.atob;
                return function (e) {
                    return !0 === e || "mobile" === e && f.default.mobile() || "phone" === e && f.default.phone() || "tablet" === e && f.default.tablet() || "function" == typeof e && !0 === e()
                }(T.disable) || t ? void w.forEach(function (e, t) {
                    e.node.removeAttribute("data-aos"), e.node.removeAttribute("data-aos-easing"), e.node.removeAttribute("data-aos-duration"), e.node.removeAttribute("data-aos-delay")
                }) : (document.querySelector("body").setAttribute("data-aos-easing", T.easing), document.querySelector("body").setAttribute("data-aos-duration", T.duration), document.querySelector("body").setAttribute("data-aos-delay", T.delay), "DOMContentLoaded" === T.startEvent && ["complete", "interactive"].indexOf(document.readyState) > -1 ? S(!0) : "load" === T.startEvent ? window.addEventListener(T.startEvent, function () {
                    S(!0)
                }) : document.addEventListener(T.startEvent, function () {
                    S(!0)
                }), window.addEventListener("resize", (0, c.default)(S, T.debounceDelay, !0)), window.addEventListener("orientationchange", (0, c.default)(S, T.debounceDelay, !0)), window.addEventListener("scroll", (0, a.default)(function () {
                    (0, m.default)(w, T.once)
                }, T.throttleDelay)), T.disableMutationObserver || (0, d.default)("[data-aos]", C), w)
            }, refresh: S, refreshHard: C
        }
    }, function (e, t) {
    }, , , , , function (e, t) {
        (function (t) {
            "use strict";

            function n(e, t, n) {
                function o(t) {
                    var n = d, i = p;
                    return d = p = void 0, v = t, h = e.apply(i, n)
                }

                function s(e) {
                    var n = e - g, i = e - v;
                    return void 0 === g || n >= t || n < 0 || b && i >= f
                }

                function l() {
                    var e = T();
                    return s(e) ? c(e) : void (m = setTimeout(l, function (e) {
                        var n = t - (e - g);
                        return b ? x(n, f - (e - v)) : n
                    }(e)))
                }

                function c(e) {
                    return m = void 0, S && d ? o(e) : (d = p = void 0, h)
                }

                function u() {
                    var e = T(), n = s(e);
                    if (d = arguments, p = this, g = e, n) {
                        if (void 0 === m) return function (e) {
                            return v = e, m = setTimeout(l, t), y ? o(e) : h
                        }(g);
                        if (b) return m = setTimeout(l, t), o(g)
                    }
                    return void 0 === m && (m = setTimeout(l, t)), h
                }

                var d, p, f, h, m, g, v = 0, y = !1, b = !1, S = !0;
                if ("function" != typeof e) throw new TypeError(a);
                return t = r(t) || 0, i(n) && (y = !!n.leading, f = (b = "maxWait" in n) ? w(r(n.maxWait) || 0, t) : f, S = "trailing" in n ? !!n.trailing : S), u.cancel = function () {
                    void 0 !== m && clearTimeout(m), v = 0, d = g = p = m = void 0
                }, u.flush = function () {
                    return void 0 === m ? h : c(T())
                }, u
            }

            function i(e) {
                var t = void 0 === e ? "undefined" : s(e);
                return !!e && ("object" == t || "function" == t)
            }

            function o(e) {
                return "symbol" == (void 0 === e ? "undefined" : s(e)) || function (e) {
                    return !!e && "object" == (void 0 === e ? "undefined" : s(e))
                }(e) && b.call(e) == c
            }

            function r(e) {
                if ("number" == typeof e) return e;
                if (o(e)) return l;
                if (i(e)) {
                    var t = "function" == typeof e.valueOf ? e.valueOf() : e;
                    e = i(t) ? t + "" : t
                }
                if ("string" != typeof e) return 0 === e ? e : +e;
                e = e.replace(u, "");
                var n = p.test(e);
                return n || f.test(e) ? h(e.slice(2), n ? 2 : 8) : d.test(e) ? l : +e
            }

            var s = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (e) {
                    return typeof e
                } : function (e) {
                    return e && "function" == typeof Symbol && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e
                }, a = "Expected a function", l = NaN, c = "[object Symbol]", u = /^\s+|\s+$/g, d = /^[-+]0x[0-9a-f]+$/i,
                p = /^0b[01]+$/i, f = /^0o[0-7]+$/i, h = parseInt,
                m = "object" == (void 0 === t ? "undefined" : s(t)) && t && t.Object === Object && t,
                g = "object" == ("undefined" == typeof self ? "undefined" : s(self)) && self && self.Object === Object && self,
                v = m || g || Function("return this")(), y = Object.prototype, b = y.toString, w = Math.max,
                x = Math.min, T = function () {
                    return v.Date.now()
                };
            e.exports = function (e, t, o) {
                var r = !0, s = !0;
                if ("function" != typeof e) throw new TypeError(a);
                return i(o) && (r = "leading" in o ? !!o.leading : r, s = "trailing" in o ? !!o.trailing : s), n(e, t, {
                    leading: r,
                    maxWait: t,
                    trailing: s
                })
            }
        }).call(t, function () {
            return this
        }())
    }, function (e, t) {
        (function (t) {
            "use strict";

            function n(e) {
                var t = void 0 === e ? "undefined" : r(e);
                return !!e && ("object" == t || "function" == t)
            }

            function i(e) {
                return "symbol" == (void 0 === e ? "undefined" : r(e)) || function (e) {
                    return !!e && "object" == (void 0 === e ? "undefined" : r(e))
                }(e) && y.call(e) == l
            }

            function o(e) {
                if ("number" == typeof e) return e;
                if (i(e)) return a;
                if (n(e)) {
                    var t = "function" == typeof e.valueOf ? e.valueOf() : e;
                    e = n(t) ? t + "" : t
                }
                if ("string" != typeof e) return 0 === e ? e : +e;
                e = e.replace(c, "");
                var o = d.test(e);
                return o || p.test(e) ? f(e.slice(2), o ? 2 : 8) : u.test(e) ? a : +e
            }

            var r = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (e) {
                    return typeof e
                } : function (e) {
                    return e && "function" == typeof Symbol && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e
                }, s = "Expected a function", a = NaN, l = "[object Symbol]", c = /^\s+|\s+$/g, u = /^[-+]0x[0-9a-f]+$/i,
                d = /^0b[01]+$/i, p = /^0o[0-7]+$/i, f = parseInt,
                h = "object" == (void 0 === t ? "undefined" : r(t)) && t && t.Object === Object && t,
                m = "object" == ("undefined" == typeof self ? "undefined" : r(self)) && self && self.Object === Object && self,
                g = h || m || Function("return this")(), v = Object.prototype, y = v.toString, b = Math.max,
                w = Math.min, x = function () {
                    return g.Date.now()
                };
            e.exports = function (e, t, i) {
                function r(t) {
                    var n = d, i = p;
                    return d = p = void 0, v = t, h = e.apply(i, n)
                }

                function a(e) {
                    var n = e - g, i = e - v;
                    return void 0 === g || n >= t || n < 0 || T && i >= f
                }

                function l() {
                    var e = x();
                    return a(e) ? c(e) : void (m = setTimeout(l, function (e) {
                        var n = t - (e - g);
                        return T ? w(n, f - (e - v)) : n
                    }(e)))
                }

                function c(e) {
                    return m = void 0, S && d ? r(e) : (d = p = void 0, h)
                }

                function u() {
                    var e = x(), n = a(e);
                    if (d = arguments, p = this, g = e, n) {
                        if (void 0 === m) return function (e) {
                            return v = e, m = setTimeout(l, t), y ? r(e) : h
                        }(g);
                        if (T) return m = setTimeout(l, t), r(g)
                    }
                    return void 0 === m && (m = setTimeout(l, t)), h
                }

                var d, p, f, h, m, g, v = 0, y = !1, T = !1, S = !0;
                if ("function" != typeof e) throw new TypeError(s);
                return t = o(t) || 0, n(i) && (y = !!i.leading, f = (T = "maxWait" in i) ? b(o(i.maxWait) || 0, t) : f, S = "trailing" in i ? !!i.trailing : S), u.cancel = function () {
                    void 0 !== m && clearTimeout(m), v = 0, d = g = p = m = void 0
                }, u.flush = function () {
                    return void 0 === m ? h : c(x())
                }, u
            }
        }).call(t, function () {
            return this
        }())
    }, function (e, t) {
        "use strict";

        function n(e) {
            e && e.forEach(function (e) {
                var t = Array.prototype.slice.call(e.addedNodes), n = Array.prototype.slice.call(e.removedNodes),
                    o = t.concat(n);
                if (function e(t) {
                    var n = void 0, i = void 0;
                    for (n = 0; n < t.length; n += 1) {
                        if ((i = t[n]).dataset && i.dataset.aos) return !0;
                        if (i.children && e(i.children)) return !0
                    }
                    return !1
                }(o)) return i()
            })
        }

        Object.defineProperty(t, "__esModule", {value: !0});
        var i = function () {
        };
        t.default = function (e, t) {
            var o = window.document,
                r = new (window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver)(n);
            i = t, r.observe(o.documentElement, {childList: !0, subtree: !0, removedNodes: !0})
        }
    }, function (e, t) {
        "use strict";

        function n() {
            return navigator.userAgent || navigator.vendor || window.opera || ""
        }

        Object.defineProperty(t, "__esModule", {value: !0});
        var i = function () {
                function e(e, t) {
                    for (var n = 0; n < t.length; n++) {
                        var i = t[n];
                        i.enumerable = i.enumerable || !1, i.configurable = !0, "value" in i && (i.writable = !0), Object.defineProperty(e, i.key, i)
                    }
                }

                return function (t, n, i) {
                    return n && e(t.prototype, n), i && e(t, i), t
                }
            }(),
            o = /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i,
            r = /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i,
            s = /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i,
            a = /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i,
            l = function () {
                function e() {
                    !function (e, t) {
                        if (!(e instanceof t)) throw new TypeError("Cannot call a class as a function")
                    }(this, e)
                }

                return i(e, [{
                    key: "phone", value: function () {
                        var e = n();
                        return !(!o.test(e) && !r.test(e.substr(0, 4)))
                    }
                }, {
                    key: "mobile", value: function () {
                        var e = n();
                        return !(!s.test(e) && !a.test(e.substr(0, 4)))
                    }
                }, {
                    key: "tablet", value: function () {
                        return this.mobile() && !this.phone()
                    }
                }]), e
            }();
        t.default = new l
    }, function (e, t) {
        "use strict";
        Object.defineProperty(t, "__esModule", {value: !0}), t.default = function (e, t) {
            var n = window.pageYOffset, i = window.innerHeight;
            e.forEach(function (e, o) {
                !function (e, t, n) {
                    var i = e.node.getAttribute("data-aos-once");
                    t > e.position ? e.node.classList.add("aos-animate") : void 0 !== i && ("false" === i || !n && "true" !== i) && e.node.classList.remove("aos-animate")
                }(e, i + n, t)
            })
        }
    }, function (e, t, n) {
        "use strict";
        Object.defineProperty(t, "__esModule", {value: !0});
        var i = n(12), o = function (e) {
            return e && e.__esModule ? e : {default: e}
        }(i);
        t.default = function (e, t) {
            return e.forEach(function (e, n) {
                e.node.classList.add("aos-init"), e.position = (0, o.default)(e.node, t.offset)
            }), e
        }
    }, function (e, t, n) {
        "use strict";
        Object.defineProperty(t, "__esModule", {value: !0});
        var i = n(13), o = function (e) {
            return e && e.__esModule ? e : {default: e}
        }(i);
        t.default = function (e, t) {
            var n = 0, i = 0, r = window.innerHeight, s = {
                offset: e.getAttribute("data-aos-offset"),
                anchor: e.getAttribute("data-aos-anchor"),
                anchorPlacement: e.getAttribute("data-aos-anchor-placement")
            };
            switch (s.offset && !isNaN(s.offset) && (i = parseInt(s.offset)), s.anchor && document.querySelectorAll(s.anchor) && (e = document.querySelectorAll(s.anchor)[0]), n = (0, o.default)(e).top, s.anchorPlacement) {
                case"top-bottom":
                    break;
                case"center-bottom":
                    n += e.offsetHeight / 2;
                    break;
                case"bottom-bottom":
                    n += e.offsetHeight;
                    break;
                case"top-center":
                    n += r / 2;
                    break;
                case"bottom-center":
                    n += r / 2 + e.offsetHeight;
                    break;
                case"center-center":
                    n += r / 2 + e.offsetHeight / 2;
                    break;
                case"top-top":
                    n += r;
                    break;
                case"bottom-top":
                    n += e.offsetHeight + r;
                    break;
                case"center-top":
                    n += e.offsetHeight / 2 + r
            }
            return s.anchorPlacement || s.offset || isNaN(t) || (i = t), n + i
        }
    }, function (e, t) {
        "use strict";
        Object.defineProperty(t, "__esModule", {value: !0}), t.default = function (e) {
            for (var t = 0, n = 0; e && !isNaN(e.offsetLeft) && !isNaN(e.offsetTop);) t += e.offsetLeft - ("BODY" != e.tagName ? e.scrollLeft : 0), n += e.offsetTop - ("BODY" != e.tagName ? e.scrollTop : 0), e = e.offsetParent;
            return {top: n, left: t}
        }
    }, function (e, t) {
        "use strict";
        Object.defineProperty(t, "__esModule", {value: !0}), t.default = function (e) {
            return e = e || document.querySelectorAll("[data-aos]"), Array.prototype.map.call(e, function (e) {
                return {node: e}
            })
        }
    }])
}, function (e, t) {
    !function (e) {
        page.registerVendor("Constellation"), page.initConstellation = function () {
            var t = 120;
            e(window).width() < 700 && (t = 25), e(".constellation").each(function () {
                var n = e(this), i = n.dataAttr("color", "rgba(255, 255, 255, .8)"), o = n.dataAttr("length", 150),
                    r = n.dataAttr("radius", 150);
                "dark" == i && (i = "rgba(0, 0, 0, .6)"), n.constellation({
                    distance: t,
                    length: o,
                    radius: r,
                    star: {color: i, width: 1},
                    line: {color: i, width: .2}
                })
            })
        }
    }(jQuery),
        /*!
 * Mantis.js / jQuery / Zepto.js plugin for Constellation
 * @version 1.2.2
 * @author Acauã Montiel <contato@acauamontiel.com.br>
 * @license http://acaua.mit-license.org/
 */
        function (e, t) {
            e.fn.constellation = function (n) {
                return this.each(function () {
                    new function (n, i) {
                        var o = e(n), r = n.getContext("2d"), s = {
                            star: {color: "rgba(255, 255, 255, .5)", width: 1},
                            line: {color: "rgba(255, 255, 255, .5)", width: .2},
                            position: {x: 0, y: 0},
                            width: t.innerWidth,
                            height: t.innerHeight,
                            velocity: .1,
                            length: 100,
                            distance: 120,
                            radius: 150,
                            stars: []
                        }, a = e.extend(!0, {}, s, i);

                        function l() {
                            this.x = Math.random() * n.width, this.y = Math.random() * n.height, this.vx = a.velocity - .5 * Math.random(), this.vy = a.velocity - .5 * Math.random(), this.radius = Math.random() * a.star.width
                        }

                        l.prototype = {
                            create: function () {
                                r.beginPath(), r.arc(this.x, this.y, this.radius, 0, 2 * Math.PI, !1), r.fill()
                            }, animate: function () {
                                var e;
                                for (e = 0; e < a.length; e++) {
                                    var t = a.stars[e];
                                    t.y < 0 || t.y > n.height ? (t.vx = t.vx, t.vy = -t.vy) : (t.x < 0 || t.x > n.width) && (t.vx = -t.vx, t.vy = t.vy), t.x += t.vx, t.y += t.vy
                                }
                            }, line: function () {
                                var e, t, n, i, o = a.length;
                                for (n = 0; n < o; n++) for (i = 0; i < o; i++) e = a.stars[n], t = a.stars[i], e.x - t.x < a.distance && e.y - t.y < a.distance && e.x - t.x > -a.distance && e.y - t.y > -a.distance && e.x - a.position.x < a.radius && e.y - a.position.y < a.radius && e.x - a.position.x > -a.radius && e.y - a.position.y > -a.radius && (r.beginPath(), r.moveTo(e.x, e.y), r.lineTo(t.x, t.y), r.stroke(), r.closePath())
                            }
                        }, this.createStars = function () {
                            var e, t, i = a.length;
                            for (r.clearRect(0, 0, n.width, n.height), t = 0; t < i; t++) a.stars.push(new l), (e = a.stars[t]).create();
                            e.line(), e.animate()
                        }, this.setCanvas = function () {
                            n.width = a.width, n.height = a.height
                        }, this.setContext = function () {
                            r.fillStyle = a.star.color, r.strokeStyle = a.line.color, r.lineWidth = a.line.width
                        }, this.setInitialPosition = function () {
                            i && i.hasOwnProperty("position") || (a.position = {x: .5 * n.width, y: .5 * n.height})
                        }, this.loop = function (e) {
                            e(), t.requestAnimationFrame(function () {
                                this.loop(e)
                            }.bind(this))
                        }, this.bind = function () {
                            o.on("mousemove", function (e) {
                                a.position.x = e.pageX - o.offset().left, a.position.y = e.pageY - o.offset().top
                            })
                        }, this.init = function () {
                            this.setCanvas(), this.setContext(), this.setInitialPosition(), this.loop(this.createStars), this.bind()
                        }
                    }(this, n).init()
                })
            }
        }($, window)
}, function (e, t, n) {
    n(16), function (e) {
        page.registerVendor("Countdown"), page.initCountdown = function () {
            e("[data-countdown]").each(function () {
                var t = e(this), n = "",
                    i = {textDay: "Day", textHour: "Hour", textMinute: "Minute", textSecond: "Second"};
                i = e.extend(i, page.getDataOptions(t)), n += '<div class="row gap-x-4">', n += '<div class="col"><h5>%D</h5><small>' + i.textDay + "%!D</small></div>", n += '<div class="col"><h5>%H</h5><small>' + i.textHour + "%!H</small></div>", n += '<div class="col"><h5>%M</h5><small>' + i.textMinute + "%!M</small></div>", n += '<div class="col"><h5>%S</h5><small>' + i.textSecond + "%!S</small></div>", n += "</div>", t.hasDataAttr("format") && (n = t.data("format")), t.countdown(t.data("countdown"), function (t) {
                    e(this).html(t.strftime(n))
                })
            })
        }
    }(jQuery)
}, function (e, t, n) {
    var i, o, r;
    /*!
 * The Final Countdown for jQuery v2.2.0 (http://hilios.github.io/jQuery.countdown/)
 * Copyright (c) 2016 Edson Hilios
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
    /*!
 * The Final Countdown for jQuery v2.2.0 (http://hilios.github.io/jQuery.countdown/)
 * Copyright (c) 2016 Edson Hilios
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
    !function (s) {
        "use strict";
        o = [n(3)], void 0 === (r = "function" == typeof (i = function (e) {
            var t = [], n = [], i = {precision: 100, elapse: !1, defer: !1};
            n.push(/^[0-9]*$/.source), n.push(/([0-9]{1,2}\/){2}[0-9]{4}( [0-9]{1,2}(:[0-9]{2}){2})?/.source), n.push(/[0-9]{4}([\/\-][0-9]{1,2}){2}( [0-9]{1,2}(:[0-9]{2}){2})?/.source), n = new RegExp(n.join("|"));
            var o = {
                Y: "years",
                m: "months",
                n: "daysToMonth",
                d: "daysToWeek",
                w: "weeks",
                W: "weeksToMonth",
                H: "hours",
                M: "minutes",
                S: "seconds",
                D: "totalDays",
                I: "totalHours",
                N: "totalMinutes",
                T: "totalSeconds"
            };

            function r(e) {
                var t = e.toString().replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
                return new RegExp(t)
            }

            function s(e, t) {
                var n = "s", i = "";
                return e && (1 === (e = e.replace(/(:|;|\s)/gi, "").split(/\,/)).length ? n = e[0] : (i = e[0], n = e[1])), Math.abs(t) > 1 ? n : i
            }

            var a = function (n, o, r) {
                this.el = n, this.$el = e(n), this.interval = null, this.offset = {}, this.options = e.extend({}, i), this.instanceNumber = t.length, t.push(this), this.$el.data("countdown-instance", this.instanceNumber), r && ("function" == typeof r ? (this.$el.on("update.countdown", r), this.$el.on("stoped.countdown", r), this.$el.on("finish.countdown", r)) : this.options = e.extend({}, i, r)), this.setFinalDate(o), !1 === this.options.defer && this.start()
            };
            e.extend(a.prototype, {
                start: function () {
                    null !== this.interval && clearInterval(this.interval);
                    var e = this;
                    this.update(), this.interval = setInterval(function () {
                        e.update.call(e)
                    }, this.options.precision)
                }, stop: function () {
                    clearInterval(this.interval), this.interval = null, this.dispatchEvent("stoped")
                }, toggle: function () {
                    this.interval ? this.stop() : this.start()
                }, pause: function () {
                    this.stop()
                }, resume: function () {
                    this.start()
                }, remove: function () {
                    this.stop.call(this), t[this.instanceNumber] = null, delete this.$el.data().countdownInstance
                }, setFinalDate: function (e) {
                    this.finalDate = function (e) {
                        if (e instanceof Date) return e;
                        if (String(e).match(n)) return String(e).match(/^[0-9]*$/) && (e = Number(e)), String(e).match(/\-/) && (e = String(e).replace(/\-/g, "/")), new Date(e);
                        throw new Error("Couldn't cast `" + e + "` to a date object.")
                    }(e)
                }, update: function () {
                    if (0 !== this.$el.closest("html").length) {
                        var t, n = void 0 !== e._data(this.el, "events"), i = new Date;
                        t = this.finalDate.getTime() - i.getTime(), t = Math.ceil(t / 1e3), t = !this.options.elapse && t < 0 ? 0 : Math.abs(t), this.totalSecsLeft !== t && n && (this.totalSecsLeft = t, this.elapsed = i >= this.finalDate, this.offset = {
                            seconds: this.totalSecsLeft % 60,
                            minutes: Math.floor(this.totalSecsLeft / 60) % 60,
                            hours: Math.floor(this.totalSecsLeft / 60 / 60) % 24,
                            days: Math.floor(this.totalSecsLeft / 60 / 60 / 24) % 7,
                            daysToWeek: Math.floor(this.totalSecsLeft / 60 / 60 / 24) % 7,
                            daysToMonth: Math.floor(this.totalSecsLeft / 60 / 60 / 24 % 30.4368),
                            weeks: Math.floor(this.totalSecsLeft / 60 / 60 / 24 / 7),
                            weeksToMonth: Math.floor(this.totalSecsLeft / 60 / 60 / 24 / 7) % 4,
                            months: Math.floor(this.totalSecsLeft / 60 / 60 / 24 / 30.4368),
                            years: Math.abs(this.finalDate.getFullYear() - i.getFullYear()),
                            totalDays: Math.floor(this.totalSecsLeft / 60 / 60 / 24),
                            totalHours: Math.floor(this.totalSecsLeft / 60 / 60),
                            totalMinutes: Math.floor(this.totalSecsLeft / 60),
                            totalSeconds: this.totalSecsLeft
                        }, this.options.elapse || 0 !== this.totalSecsLeft ? this.dispatchEvent("update") : (this.stop(), this.dispatchEvent("finish")))
                    } else this.remove()
                }, dispatchEvent: function (t) {
                    var n = e.Event(t + ".countdown");
                    n.finalDate = this.finalDate, n.elapsed = this.elapsed, n.offset = e.extend({}, this.offset), n.strftime = function (e) {
                        return function (t) {
                            var n = t.match(/%(-|!)?[A-Z]{1}(:[^;]+;)?/gi);
                            if (n) for (var i = 0, a = n.length; i < a; ++i) {
                                var l = n[i].match(/%(-|!)?([a-zA-Z]{1})(:[^;]+;)?/), c = r(l[0]), u = l[1] || "",
                                    d = l[3] || "", p = null;
                                l = l[2], o.hasOwnProperty(l) && (p = o[l], p = Number(e[p])), null !== p && ("!" === u && (p = s(d, p)), "" === u && p < 10 && (p = "0" + p.toString()), t = t.replace(c, p.toString()))
                            }
                            return t = t.replace(/%%/, "%")
                        }
                    }(this.offset), this.$el.trigger(n)
                }
            }), e.fn.countdown = function () {
                var n = Array.prototype.slice.call(arguments, 0);
                return this.each(function () {
                    var i = e(this).data("countdown-instance");
                    if (void 0 !== i) {
                        var o = t[i], r = n[0];
                        a.prototype.hasOwnProperty(r) ? o[r].apply(o, n.slice(1)) : null === String(r).match(/^[$A-Z_][0-9A-Z_$]*$/i) ? (o.setFinalDate.call(o, r), o.start()) : e.error("Method %s does not exist on jQuery.countdown".replace(/\%s/gi, r))
                    } else new a(this, n[0], n[1])
                })
            }
        }) ? i.apply(t, o) : i) || (e.exports = r)
    }()
}, function (e, t, n) {
    n(18), window.CountUp = n(19), function (e) {
        page.registerVendor("Countup"), page.initCountup = function () {
            e('[data-provide~="countup"]:not(.counted)').each(function () {
                var t = e(this), n = {};
                n = e.extend(n, page.getDataOptions(t));
                var i = {startVal: t.dataAttr("from", 0), endVal: t.dataAttr("to", 0), options: n};
                i = e.extend(i, page.getDataOptions(t)), t.waypoint({
                    handler: function (e) {
                        t.countup(i).addClass("counted")
                    }, offset: "100%"
                })
            })
        }
    }(jQuery), $.fn.countup = function (e) {
        if ("function" == typeof CountUp) {
            var t = {startVal: 0, decimals: 0, duration: 4};
            if ("number" == typeof e) t.endVal = e; else {
                if ("object" != typeof e) return void console.error("countUp-jquery requires its argument to be either an object or number");
                $.extend(t, e)
            }
            return this.each(function (e, n) {
                new CountUp(n, t.startVal, t.endVal, t.decimals, t.duration, t.options).start()
            }), this
        }
        console.error("countUp.js is a required dependency of countUp-jquery.js.")
    }
}, function (e, t) {
    /*!
Waypoints - 4.0.1
Copyright © 2011-2016 Caleb Troughton
Licensed under the MIT license.
https://github.com/imakewebthings/waypoints/blob/master/licenses.txt
*/
    !function () {
        "use strict";
        var e = 0, t = {};

        function n(i) {
            if (!i) throw new Error("No options passed to Waypoint constructor");
            if (!i.element) throw new Error("No element option passed to Waypoint constructor");
            if (!i.handler) throw new Error("No handler option passed to Waypoint constructor");
            this.key = "waypoint-" + e, this.options = n.Adapter.extend({}, n.defaults, i), this.element = this.options.element, this.adapter = new n.Adapter(this.element), this.callback = i.handler, this.axis = this.options.horizontal ? "horizontal" : "vertical", this.enabled = this.options.enabled, this.triggerPoint = null, this.group = n.Group.findOrCreate({
                name: this.options.group,
                axis: this.axis
            }), this.context = n.Context.findOrCreateByElement(this.options.context), n.offsetAliases[this.options.offset] && (this.options.offset = n.offsetAliases[this.options.offset]), this.group.add(this), this.context.add(this), t[this.key] = this, e += 1
        }

        n.prototype.queueTrigger = function (e) {
            this.group.queueTrigger(this, e)
        }, n.prototype.trigger = function (e) {
            this.enabled && this.callback && this.callback.apply(this, e)
        }, n.prototype.destroy = function () {
            this.context.remove(this), this.group.remove(this), delete t[this.key]
        }, n.prototype.disable = function () {
            return this.enabled = !1, this
        }, n.prototype.enable = function () {
            return this.context.refresh(), this.enabled = !0, this
        }, n.prototype.next = function () {
            return this.group.next(this)
        }, n.prototype.previous = function () {
            return this.group.previous(this)
        }, n.invokeAll = function (e) {
            var n = [];
            for (var i in t) n.push(t[i]);
            for (var o = 0, r = n.length; o < r; o++) n[o][e]()
        }, n.destroyAll = function () {
            n.invokeAll("destroy")
        }, n.disableAll = function () {
            n.invokeAll("disable")
        }, n.enableAll = function () {
            for (var e in n.Context.refreshAll(), t) t[e].enabled = !0;
            return this
        }, n.refreshAll = function () {
            n.Context.refreshAll()
        }, n.viewportHeight = function () {
            return window.innerHeight || document.documentElement.clientHeight
        }, n.viewportWidth = function () {
            return document.documentElement.clientWidth
        }, n.adapters = [], n.defaults = {
            context: window,
            continuous: !0,
            enabled: !0,
            group: "default",
            horizontal: !1,
            offset: 0
        }, n.offsetAliases = {
            "bottom-in-view": function () {
                return this.context.innerHeight() - this.adapter.outerHeight()
            }, "right-in-view": function () {
                return this.context.innerWidth() - this.adapter.outerWidth()
            }
        }, window.Waypoint = n
    }(), function () {
        "use strict";

        function e(e) {
            window.setTimeout(e, 1e3 / 60)
        }

        var t = 0, n = {}, i = window.Waypoint, o = window.onload;

        function r(e) {
            this.element = e, this.Adapter = i.Adapter, this.adapter = new this.Adapter(e), this.key = "waypoint-context-" + t, this.didScroll = !1, this.didResize = !1, this.oldScroll = {
                x: this.adapter.scrollLeft(),
                y: this.adapter.scrollTop()
            }, this.waypoints = {
                vertical: {},
                horizontal: {}
            }, e.waypointContextKey = this.key, n[e.waypointContextKey] = this, t += 1, i.windowContext || (i.windowContext = !0, i.windowContext = new r(window)), this.createThrottledScrollHandler(), this.createThrottledResizeHandler()
        }

        r.prototype.add = function (e) {
            var t = e.options.horizontal ? "horizontal" : "vertical";
            this.waypoints[t][e.key] = e, this.refresh()
        }, r.prototype.checkEmpty = function () {
            var e = this.Adapter.isEmptyObject(this.waypoints.horizontal),
                t = this.Adapter.isEmptyObject(this.waypoints.vertical), i = this.element == this.element.window;
            e && t && !i && (this.adapter.off(".waypoints"), delete n[this.key])
        }, r.prototype.createThrottledResizeHandler = function () {
            var e = this;

            function t() {
                e.handleResize(), e.didResize = !1
            }

            this.adapter.on("resize.waypoints", function () {
                e.didResize || (e.didResize = !0, i.requestAnimationFrame(t))
            })
        }, r.prototype.createThrottledScrollHandler = function () {
            var e = this;

            function t() {
                e.handleScroll(), e.didScroll = !1
            }

            this.adapter.on("scroll.waypoints", function () {
                e.didScroll && !i.isTouch || (e.didScroll = !0, i.requestAnimationFrame(t))
            })
        }, r.prototype.handleResize = function () {
            i.Context.refreshAll()
        }, r.prototype.handleScroll = function () {
            var e = {}, t = {
                horizontal: {
                    newScroll: this.adapter.scrollLeft(),
                    oldScroll: this.oldScroll.x,
                    forward: "right",
                    backward: "left"
                },
                vertical: {
                    newScroll: this.adapter.scrollTop(),
                    oldScroll: this.oldScroll.y,
                    forward: "down",
                    backward: "up"
                }
            };
            for (var n in t) {
                var i = t[n], o = i.newScroll > i.oldScroll ? i.forward : i.backward;
                for (var r in this.waypoints[n]) {
                    var s = this.waypoints[n][r];
                    if (null !== s.triggerPoint) {
                        var a = i.oldScroll < s.triggerPoint, l = i.newScroll >= s.triggerPoint;
                        (a && l || !a && !l) && (s.queueTrigger(o), e[s.group.id] = s.group)
                    }
                }
            }
            for (var c in e) e[c].flushTriggers();
            this.oldScroll = {x: t.horizontal.newScroll, y: t.vertical.newScroll}
        }, r.prototype.innerHeight = function () {
            return this.element == this.element.window ? i.viewportHeight() : this.adapter.innerHeight()
        }, r.prototype.remove = function (e) {
            delete this.waypoints[e.axis][e.key], this.checkEmpty()
        }, r.prototype.innerWidth = function () {
            return this.element == this.element.window ? i.viewportWidth() : this.adapter.innerWidth()
        }, r.prototype.destroy = function () {
            var e = [];
            for (var t in this.waypoints) for (var n in this.waypoints[t]) e.push(this.waypoints[t][n]);
            for (var i = 0, o = e.length; i < o; i++) e[i].destroy()
        }, r.prototype.refresh = function () {
            var e, t = this.element == this.element.window, n = t ? void 0 : this.adapter.offset(), o = {};
            for (var r in this.handleScroll(), e = {
                horizontal: {
                    contextOffset: t ? 0 : n.left,
                    contextScroll: t ? 0 : this.oldScroll.x,
                    contextDimension: this.innerWidth(),
                    oldScroll: this.oldScroll.x,
                    forward: "right",
                    backward: "left",
                    offsetProp: "left"
                },
                vertical: {
                    contextOffset: t ? 0 : n.top,
                    contextScroll: t ? 0 : this.oldScroll.y,
                    contextDimension: this.innerHeight(),
                    oldScroll: this.oldScroll.y,
                    forward: "down",
                    backward: "up",
                    offsetProp: "top"
                }
            }) {
                var s = e[r];
                for (var a in this.waypoints[r]) {
                    var l, c, u, d, p = this.waypoints[r][a], f = p.options.offset, h = p.triggerPoint, m = 0,
                        g = null == h;
                    p.element !== p.element.window && (m = p.adapter.offset()[s.offsetProp]), "function" == typeof f ? f = f.apply(p) : "string" == typeof f && (f = parseFloat(f), p.options.offset.indexOf("%") > -1 && (f = Math.ceil(s.contextDimension * f / 100))), l = s.contextScroll - s.contextOffset, p.triggerPoint = Math.floor(m + l - f), c = h < s.oldScroll, u = p.triggerPoint >= s.oldScroll, d = !c && !u, !g && (c && u) ? (p.queueTrigger(s.backward), o[p.group.id] = p.group) : !g && d ? (p.queueTrigger(s.forward), o[p.group.id] = p.group) : g && s.oldScroll >= p.triggerPoint && (p.queueTrigger(s.forward), o[p.group.id] = p.group)
                }
            }
            return i.requestAnimationFrame(function () {
                for (var e in o) o[e].flushTriggers()
            }), this
        }, r.findOrCreateByElement = function (e) {
            return r.findByElement(e) || new r(e)
        }, r.refreshAll = function () {
            for (var e in n) n[e].refresh()
        }, r.findByElement = function (e) {
            return n[e.waypointContextKey]
        }, window.onload = function () {
            o && o(), r.refreshAll()
        }, i.requestAnimationFrame = function (t) {
            (window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame || e).call(window, t)
        }, i.Context = r
    }(), function () {
        "use strict";

        function e(e, t) {
            return e.triggerPoint - t.triggerPoint
        }

        function t(e, t) {
            return t.triggerPoint - e.triggerPoint
        }

        var n = {vertical: {}, horizontal: {}}, i = window.Waypoint;

        function o(e) {
            this.name = e.name, this.axis = e.axis, this.id = this.name + "-" + this.axis, this.waypoints = [], this.clearTriggerQueues(), n[this.axis][this.name] = this
        }

        o.prototype.add = function (e) {
            this.waypoints.push(e)
        }, o.prototype.clearTriggerQueues = function () {
            this.triggerQueues = {up: [], down: [], left: [], right: []}
        }, o.prototype.flushTriggers = function () {
            for (var n in this.triggerQueues) {
                var i = this.triggerQueues[n], o = "up" === n || "left" === n;
                i.sort(o ? t : e);
                for (var r = 0, s = i.length; r < s; r += 1) {
                    var a = i[r];
                    (a.options.continuous || r === i.length - 1) && a.trigger([n])
                }
            }
            this.clearTriggerQueues()
        }, o.prototype.next = function (t) {
            this.waypoints.sort(e);
            var n = i.Adapter.inArray(t, this.waypoints);
            return n === this.waypoints.length - 1 ? null : this.waypoints[n + 1]
        }, o.prototype.previous = function (t) {
            this.waypoints.sort(e);
            var n = i.Adapter.inArray(t, this.waypoints);
            return n ? this.waypoints[n - 1] : null
        }, o.prototype.queueTrigger = function (e, t) {
            this.triggerQueues[t].push(e)
        }, o.prototype.remove = function (e) {
            var t = i.Adapter.inArray(e, this.waypoints);
            t > -1 && this.waypoints.splice(t, 1)
        }, o.prototype.first = function () {
            return this.waypoints[0]
        }, o.prototype.last = function () {
            return this.waypoints[this.waypoints.length - 1]
        }, o.findOrCreate = function (e) {
            return n[e.axis][e.name] || new o(e)
        }, i.Group = o
    }(), function () {
        "use strict";
        var e = window.jQuery, t = window.Waypoint;

        function n(t) {
            this.$element = e(t)
        }

        e.each(["innerHeight", "innerWidth", "off", "offset", "on", "outerHeight", "outerWidth", "scrollLeft", "scrollTop"], function (e, t) {
            n.prototype[t] = function () {
                var e = Array.prototype.slice.call(arguments);
                return this.$element[t].apply(this.$element, e)
            }
        }), e.each(["extend", "inArray", "isEmptyObject"], function (t, i) {
            n[i] = e[i]
        }), t.adapters.push({name: "jquery", Adapter: n}), t.Adapter = n
    }(), function () {
        "use strict";
        var e = window.Waypoint;

        function t(t) {
            return function () {
                var n = [], i = arguments[0];
                return t.isFunction(arguments[0]) && ((i = t.extend({}, arguments[1])).handler = arguments[0]), this.each(function () {
                    var o = t.extend({}, i, {element: this});
                    "string" == typeof o.context && (o.context = t(this).closest(o.context)[0]), n.push(new e(o))
                }), n
            }
        }

        window.jQuery && (window.jQuery.fn.waypoint = t(window.jQuery)), window.Zepto && (window.Zepto.fn.waypoint = t(window.Zepto))
    }()
}, function (e, t, n) {
    var i, o;
    void 0 === (o = "function" == typeof (i = function (e, t, n) {
        return function (e, t, n, i, o, r) {
            function s(e) {
                return "number" == typeof e && !isNaN(e)
            }

            var a = this;
            if (a.version = function () {
                return "1.9.3"
            }, a.options = {
                useEasing: !0,
                useGrouping: !0,
                separator: ",",
                decimal: ".",
                easingFn: function (e, t, n, i) {
                    return n * (1 - Math.pow(2, -10 * e / i)) * 1024 / 1023 + t
                },
                formattingFn: function (e) {
                    var t, n, i, o, r, s, l = e < 0;
                    if (e = Math.abs(e).toFixed(a.decimals), t = (e += "").split("."), n = t[0], i = t.length > 1 ? a.options.decimal + t[1] : "", a.options.useGrouping) {
                        for (o = "", r = 0, s = n.length; r < s; ++r) 0 !== r && r % 3 == 0 && (o = a.options.separator + o), o = n[s - r - 1] + o;
                        n = o
                    }
                    return a.options.numerals.length && (n = n.replace(/[0-9]/g, function (e) {
                        return a.options.numerals[+e]
                    }), i = i.replace(/[0-9]/g, function (e) {
                        return a.options.numerals[+e]
                    })), (l ? "-" : "") + a.options.prefix + n + i + a.options.suffix
                },
                prefix: "",
                suffix: "",
                numerals: []
            }, r && "object" == typeof r) for (var l in a.options) r.hasOwnProperty(l) && null !== r[l] && (a.options[l] = r[l]);
            "" === a.options.separator ? a.options.useGrouping = !1 : a.options.separator = "" + a.options.separator;
            for (var c = 0, u = ["webkit", "moz", "ms", "o"], d = 0; d < u.length && !window.requestAnimationFrame; ++d) window.requestAnimationFrame = window[u[d] + "RequestAnimationFrame"], window.cancelAnimationFrame = window[u[d] + "CancelAnimationFrame"] || window[u[d] + "CancelRequestAnimationFrame"];
            window.requestAnimationFrame || (window.requestAnimationFrame = function (e, t) {
                var n = (new Date).getTime(), i = Math.max(0, 16 - (n - c)), o = window.setTimeout(function () {
                    e(n + i)
                }, i);
                return c = n + i, o
            }), window.cancelAnimationFrame || (window.cancelAnimationFrame = function (e) {
                clearTimeout(e)
            }), a.initialize = function () {
                return !(!a.initialized && (a.error = "", a.d = "string" == typeof e ? document.getElementById(e) : e, a.d ? (a.startVal = Number(t), a.endVal = Number(n), s(a.startVal) && s(a.endVal) ? (a.decimals = Math.max(0, i || 0), a.dec = Math.pow(10, a.decimals), a.duration = 1e3 * Number(o) || 2e3, a.countDown = a.startVal > a.endVal, a.frameVal = a.startVal, a.initialized = !0, 0) : (a.error = "[CountUp] startVal (" + t + ") or endVal (" + n + ") is not a number", 1)) : (a.error = "[CountUp] target is null or undefined", 1)))
            }, a.printValue = function (e) {
                var t = a.options.formattingFn(e);
                "INPUT" === a.d.tagName ? this.d.value = t : "text" === a.d.tagName || "tspan" === a.d.tagName ? this.d.textContent = t : this.d.innerHTML = t
            }, a.count = function (e) {
                a.startTime || (a.startTime = e), a.timestamp = e;
                var t = e - a.startTime;
                a.remaining = a.duration - t, a.options.useEasing ? a.countDown ? a.frameVal = a.startVal - a.options.easingFn(t, 0, a.startVal - a.endVal, a.duration) : a.frameVal = a.options.easingFn(t, a.startVal, a.endVal - a.startVal, a.duration) : a.countDown ? a.frameVal = a.startVal - (a.startVal - a.endVal) * (t / a.duration) : a.frameVal = a.startVal + (a.endVal - a.startVal) * (t / a.duration), a.countDown ? a.frameVal = a.frameVal < a.endVal ? a.endVal : a.frameVal : a.frameVal = a.frameVal > a.endVal ? a.endVal : a.frameVal, a.frameVal = Math.round(a.frameVal * a.dec) / a.dec, a.printValue(a.frameVal), t < a.duration ? a.rAF = requestAnimationFrame(a.count) : a.callback && a.callback()
            }, a.start = function (e) {
                a.initialize() && (a.callback = e, a.rAF = requestAnimationFrame(a.count))
            }, a.pauseResume = function () {
                a.paused ? (a.paused = !1, delete a.startTime, a.duration = a.remaining, a.startVal = a.frameVal, requestAnimationFrame(a.count)) : (a.paused = !0, cancelAnimationFrame(a.rAF))
            }, a.reset = function () {
                a.paused = !1, delete a.startTime, a.initialized = !1, a.initialize() && (cancelAnimationFrame(a.rAF), a.printValue(a.startVal))
            }, a.update = function (e) {
                if (a.initialize()) {
                    if (!s(e = Number(e))) return void (a.error = "[CountUp] update() - new endVal is not a number: " + e);
                    a.error = "", e !== a.frameVal && (cancelAnimationFrame(a.rAF), a.paused = !1, delete a.startTime, a.startVal = a.frameVal, a.endVal = e, a.countDown = a.startVal > a.endVal, a.rAF = requestAnimationFrame(a.count))
                }
            }, a.initialize() && a.printValue(a.startVal)
        }
    }) ? i.call(t, n, t, e) : i) || (e.exports = o)
}, function (e, t, n) {
    window.Granim = n(21), function (e) {
        page.registerVendor("Granim"), page.initGranim = function () {
            e("[data-granim]").each(function () {
                var t = e(this), n = t.data("granim").split(","), i = [], o = [1, 1], r = n.length;
                if (r > 0) if (n[0].indexOf("-") > -1) {
                    for (var s = 0; s < r; s++) i[s] = n[s].split("-");
                    for (s = 0; s < i[0].length; s++) o[s] = 1
                } else for (s = 0; s < r / 2; s++) i[s] = [n[2 * s], n[2 * s + 1]];
                var a = {
                    element: t[0],
                    name: "granim",
                    direction: t.dataAttr("direction", "left-right"),
                    isPausedWhenNotInView: !0,
                    opacity: o,
                    states: {"default-state": {gradients: i, transitionSpeed: 5e3, loop: !0}}
                };
                t.hasDataAttr("opacity") && (a.opacity = t.data("opacity").split(",")), t.hasDataAttr("image") && (a.image = {
                    source: t.dataAttr("image", ""),
                    position: ["center", "center"],
                    stretchMode: ["stretch-if-bigger", "stretch-if-bigger"],
                    blendingMode: "multiply"
                });
                new Granim(a)
            })
        }
    }(jQuery)
}, function (e, t, n) {
    e.exports = n(22)
}, function (e, t, n) {
    "use strict";

    function i(e) {
        var t;
        this.getElement(e.element), this.x1 = 0, this.y1 = 0, this.name = e.name || !1, this.elToSetClassOn = e.elToSetClassOn || "body", this.direction = e.direction || "diagonal", this.isPausedWhenNotInView = e.isPausedWhenNotInView || !1, this.opacity = e.opacity, this.states = e.states, this.stateTransitionSpeed = e.stateTransitionSpeed || 1e3, this.previousTimeStamp = null, this.progress = 0, this.isPaused = !1, this.isCleared = !1, this.isPausedBecauseNotInView = !1, this.iscurrentColorsSet = !1, this.context = this.canvas.getContext("2d"), this.channels = {}, this.channelsIndex = 0, this.activeState = e.defaultStateName || "default-state", this.isChangingState = !1, this.activeColors = [], this.activeColorDiff = [], this.activetransitionSpeed = null, this.currentColors = [], this.eventPolyfill(), this.scrollDebounceThreshold = e.scrollDebounceThreshold || 300, this.scrollDebounceTimeout = null, this.isImgLoaded = !1, this.isCanvasInWindowView = !1, this.firstScrollInit = !0, this.animating = !1, e.image && e.image.source && (this.image = {
            source: e.image.source,
            position: e.image.position || ["center", "center"],
            stretchMode: e.image.stretchMode || !1,
            blendingMode: e.image.blendingMode || !1
        }), t = -1 !== this.opacity.map(function (e) {
            return 1 !== e
        }).indexOf(!0), this.shouldClearCanvasOnEachFrame = !!this.image || t, this.events = {
            start: new CustomEvent("granim:start"),
            end: new CustomEvent("granim:end"),
            gradientChange: function (e) {
                return new CustomEvent("granim:gradientChange", {
                    detail: {
                        isLooping: e.isLooping,
                        colorsFrom: e.colorsFrom,
                        colorsTo: e.colorsTo,
                        activeState: e.activeState
                    }, bubbles: !1, cancelable: !1
                })
            }
        }, this.callbacks = {
            onStart: "function" == typeof e.onStart && e.onStart,
            onGradientChange: "function" == typeof e.onGradientChange && e.onGradientChange,
            onEnd: "function" == typeof e.onEnd && e.onEnd
        }, this.getDimensions(), this.canvas.setAttribute("width", this.x1), this.canvas.setAttribute("height", this.y1), this.setColors(), this.image && (this.validateInput("image"), this.prepareImage()), this.pauseWhenNotInViewNameSpace = this.pauseWhenNotInView.bind(this), this.setSizeAttributesNameSpace = this.setSizeAttributes.bind(this), this.onResize(), this.isPausedWhenNotInView ? this.onScroll() : this.image || (this.refreshColors(), this.animation = requestAnimationFrame(this.animateColors.bind(this)), this.animating = !0), this.callbacks.onStart && this.callbacks.onStart(), this.canvas.dispatchEvent(this.events.start)
    }

    i.prototype.onResize = n(23), i.prototype.onScroll = n(24), i.prototype.validateInput = n(25), i.prototype.triggerError = n(26), i.prototype.prepareImage = n(27), i.prototype.eventPolyfill = n(28), i.prototype.colorDiff = n(29), i.prototype.hexToRgb = n(30), i.prototype.setDirection = n(31), i.prototype.setColors = n(32), i.prototype.getElement = n(33), i.prototype.getDimensions = n(34), i.prototype.getLightness = n(35), i.prototype.getCurrentColors = n(36), i.prototype.animateColors = n(37), i.prototype.refreshColors = n(38), i.prototype.makeGradient = n(39), i.prototype.pause = n(40), i.prototype.play = n(41), i.prototype.clear = n(42), i.prototype.destroy = n(43), i.prototype.pauseWhenNotInView = n(44), i.prototype.setSizeAttributes = n(45), i.prototype.changeDirection = n(46), i.prototype.changeBlendingMode = n(47), i.prototype.changeState = n(48), e.exports = i
}, function (e, t, n) {
    "use strict";
    e.exports = function (e) {
        "removeListeners" !== e ? window.addEventListener("resize", this.setSizeAttributesNameSpace) : window.removeEventListener("resize", this.setSizeAttributesNameSpace)
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function (e) {
        "removeListeners" !== e ? (window.addEventListener("scroll", this.pauseWhenNotInViewNameSpace), this.pauseWhenNotInViewNameSpace()) : window.removeEventListener("scroll", this.pauseWhenNotInViewNameSpace)
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function (e) {
        var t = ["stretch", "stretch-if-smaller", "stretch-if-bigger"];
        switch (e) {
            case"image":
                Array.isArray(this.image.position) && 2 === this.image.position.length && -1 !== ["left", "center", "right"].indexOf(this.image.position[0]) && -1 !== ["top", "center", "bottom"].indexOf(this.image.position[1]) || this.triggerError("image.position"), this.image.stretchMode && (Array.isArray(this.image.stretchMode) && 2 === this.image.stretchMode.length && -1 !== t.indexOf(this.image.stretchMode[0]) && -1 !== t.indexOf(this.image.stretchMode[1]) || this.triggerError("image.stretchMode"));
                break;
            case"blendingMode":
                -1 === ["multiply", "screen", "normal", "overlay", "darken", "lighten", "lighter", "color-dodge", "color-burn", "hard-light", "soft-light", "difference", "exclusion", "hue", "saturation", "color", "luminosity"].indexOf(this.image.blendingMode) && (this.clear(), this.triggerError("blendingMode"))
        }
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function (e) {
        throw new Error('Granim: Input error on "' + e + '" option.\nCheck the API https://sarcadass.github.io/granim.js/api.html.')
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function () {
        var e = this;

        function t() {
            var t;
            for (t = 0; t < 2; t++) n(t ? "y" : "x");

            function n(t) {
                var n, i = e[t + "1"], o = e["x" === t ? "imgOriginalWidth" : "imgOriginalHeight"],
                    r = "x" === t ? e.image.position[0] : e.image.position[1];
                switch (r) {
                    case"center":
                        n = o > i ? -(o - i) / 2 : (i - o) / 2, e.imagePosition[t] = n, e.imagePosition["x" === t ? "width" : "height"] = o;
                        break;
                    case"top":
                        e.imagePosition.y = 0, e.imagePosition.height = o;
                        break;
                    case"bottom":
                        e.imagePosition.y = i - o, e.imagePosition.height = o;
                        break;
                    case"right":
                        e.imagePosition.x = i - o, e.imagePosition.width = o;
                        break;
                    case"left":
                        e.imagePosition.x = 0, e.imagePosition.width = o
                }
                if (e.image.stretchMode) switch (r = "x" === t ? e.image.stretchMode[0] : e.image.stretchMode[1]) {
                    case"stretch":
                        e.imagePosition[t] = 0, e.imagePosition["x" === t ? "width" : "height"] = i;
                        break;
                    case"stretch-if-bigger":
                        if (o < i) break;
                        e.imagePosition[t] = 0, e.imagePosition["x" === t ? "width" : "height"] = i;
                        break;
                    case"stretch-if-smaller":
                        if (o > i) break;
                        e.imagePosition[t] = 0, e.imagePosition["x" === t ? "width" : "height"] = i
                }
            }
        }

        this.imagePosition || (this.imagePosition = {
            x: 0,
            y: 0,
            width: 0,
            height: 0
        }), this.image.blendingMode && (this.context.globalCompositeOperation = this.image.blendingMode), this.imageNode ? t() : (this.imageNode = new Image, this.imageNode.onerror = function () {
            throw new Error("Granim: The image source is invalid.")
        }, this.imageNode.onload = function () {
            e.imgOriginalWidth = e.imageNode.width, e.imgOriginalHeight = e.imageNode.height, t(), e.refreshColors(), e.isPausedWhenNotInView && !e.isCanvasInWindowView || (e.animation = requestAnimationFrame(e.animateColors.bind(e))), e.isImgLoaded = !0
        }, this.imageNode.src = this.image.source)
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function () {
        function e(e, t) {
            t = t || {bubbles: !1, cancelable: !1, detail: void 0};
            var n = document.createEvent("CustomEvent");
            return n.initCustomEvent(e, t.bubbles, t.cancelable, t.detail), n
        }

        "function" != typeof window.CustomEvent && (e.prototype = window.Event.prototype, window.CustomEvent = e)
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function (e, t) {
        var n, i = [];
        for (n = 0; n < 3; n++) i.push(t[n] - e[n]);
        return i
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function (e) {
        e = e.replace(/^#?([a-f\d])([a-f\d])([a-f\d])$/i, function (e, t, n, i) {
            return t + t + n + n + i + i
        });
        var t = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(e);
        return t ? [parseInt(t[1], 16), parseInt(t[2], 16), parseInt(t[3], 16)] : null
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function () {
        var e = this.context;
        switch (this.direction) {
            default:
                this.triggerError("direction");
                break;
            case"diagonal":
                return e.createLinearGradient(0, 0, this.x1, this.y1);
            case"left-right":
                return e.createLinearGradient(0, 0, this.x1, 0);
            case"top-bottom":
                return e.createLinearGradient(this.x1 / 2, 0, this.x1 / 2, this.y1);
            case"radial":
                return e.createRadialGradient(this.x1 / 2, this.y1 / 2, this.x1 / 2, this.x1 / 2, this.y1 / 2, 0)
        }
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function () {
        var e, t, n = this;
        if (this.channels[this.activeState] || (this.channels[this.activeState] = []), void 0 !== this.channels[this.activeState][this.channelsIndex]) return this.activeColors = this.channels[this.activeState][this.channelsIndex].colors, void (this.activeColorDiff = this.channels[this.activeState][this.channelsIndex].colorsDiff);
        this.channels[this.activeState].push([{}]), this.channels[this.activeState][this.channelsIndex].colors = [], this.channels[this.activeState][this.channelsIndex].colorsDiff = [], this.activeColors = [], this.activeColorDiff = [], this.states[this.activeState].gradients[this.channelsIndex].forEach(function (i, o) {
            var r = n.hexToRgb(i), s = n.channels[n.activeState];
            s[n.channelsIndex].colors.push(r), n.activeColors.push(r), n.iscurrentColorsSet || n.currentColors.push(n.hexToRgb(i)), n.channelsIndex === n.states[n.activeState].gradients.length - 1 ? e = n.colorDiff(s[n.channelsIndex].colors[o], s[0].colors[o]) : (t = n.hexToRgb(n.states[n.activeState].gradients[n.channelsIndex + 1][o]), e = n.colorDiff(s[n.channelsIndex].colors[o], t)), s[n.channelsIndex].colorsDiff.push(e), n.activeColorDiff.push(e)
        }), this.activetransitionSpeed = this.states[this.activeState].transitionSpeed || 5e3, this.iscurrentColorsSet = !0
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function (e) {
        if (e instanceof HTMLCanvasElement) this.canvas = e; else {
            if ("string" != typeof e) throw new Error("The element you used is neither a String, nor a HTMLCanvasElement");
            this.canvas = document.querySelector(e)
        }
        if (!this.canvas) throw new Error("`" + e + "` could not be found in the DOM")
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function () {
        this.x1 = this.canvas.offsetWidth, this.y1 = this.canvas.offsetHeight
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function () {
        var e, t, n = null, i = this.getCurrentColors().map(function (e) {
            return Math.max(e[0], e[1], e[2])
        });
        for (t = 0; t < i.length; t++) n = null === n ? i[t] : n + i[t], t === i.length - 1 && (e = Math.round(n / (t + 1)));
        return e >= 128 ? "light" : "dark"
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function () {
        var e, t, n = [];
        for (e = 0; e < this.currentColors.length; e++) for (n.push([]), t = 0; t < 3; t++) n[e].push(this.currentColors[e][t]);
        return n
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function (e) {
        var t, n, i, o = e - this.previousTimeStamp > 100,
            r = void 0 === this.states[this.activeState].loop || this.states[this.activeState].loop;
        (null === this.previousTimeStamp || o) && (this.previousTimeStamp = e), this.progress = this.progress + (e - this.previousTimeStamp), t = (this.progress / this.activetransitionSpeed * 100).toFixed(2), this.previousTimeStamp = e, this.refreshColors(t), t < 100 ? this.animation = requestAnimationFrame(this.animateColors.bind(this)) : this.channelsIndex < this.states[this.activeState].gradients.length - 2 || r ? (this.isChangingState && (this.activetransitionSpeed = this.states[this.activeState].transitionSpeed || 5e3), this.previousTimeStamp = null, this.progress = 0, this.channelsIndex++, n = !1, this.channelsIndex === this.states[this.activeState].gradients.length - 1 ? n = !0 : this.channelsIndex === this.states[this.activeState].gradients.length && (this.channelsIndex = 0), i = void 0 === this.states[this.activeState].gradients[this.channelsIndex + 1] ? this.states[this.activeState].gradients[0] : this.states[this.activeState].gradients[this.channelsIndex + 1], this.setColors(), this.animation = requestAnimationFrame(this.animateColors.bind(this)), this.callbacks.onGradientChange && this.callbacks.onGradientChange({
            isLooping: n,
            colorsFrom: this.states[this.activeState].gradients[this.channelsIndex],
            colorsTo: i,
            activeState: this.activeState
        }), this.canvas.dispatchEvent(this.events.gradientChange({
            isLooping: n,
            colorsFrom: this.states[this.activeState].gradients[this.channelsIndex],
            colorsTo: i,
            activeState: this.activeState
        }))) : (cancelAnimationFrame(this.animation), this.callbacks.onEnd && this.callbacks.onEnd(), this.canvas.dispatchEvent(new CustomEvent("granim:end")))
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function (e) {
        var t, n, i;
        for (n = 0; n < this.activeColors.length; n++) for (i = 0; i < 3; i++) (t = this.activeColors[n][i] + Math.ceil(this.activeColorDiff[n][i] / 100 * e)) <= 255 && t >= 0 && (this.currentColors[n][i] = t);
        this.makeGradient()
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function () {
        var e, t, n = this.setDirection(), i = document.querySelector(this.elToSetClassOn).classList;
        for (this.shouldClearCanvasOnEachFrame && this.context.clearRect(0, 0, this.x1, this.y1), this.image && this.context.drawImage(this.imageNode, this.imagePosition.x, this.imagePosition.y, this.imagePosition.width, this.imagePosition.height), e = 0; e < this.currentColors.length; e++) t = e ? (1 / (this.currentColors.length - 1) * e).toFixed(2) : 0, n.addColorStop(t, "rgba(" + this.currentColors[e][0] + ", " + this.currentColors[e][1] + ", " + this.currentColors[e][2] + ", " + this.opacity[e] + ")");
        this.name && ("light" === this.getLightness() ? (i.remove(this.name + "-dark"), i.add(this.name + "-light")) : (i.remove(this.name + "-light"), i.add(this.name + "-dark"))), this.context.fillStyle = n, this.context.fillRect(0, 0, this.x1, this.y1)
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function (e) {
        var t = "isPausedBecauseNotInView" === e;
        this.isCleared || (t || (this.isPaused = !0), cancelAnimationFrame(this.animation), this.animating = !1)
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function (e) {
        "isPlayedBecauseInView" === e || (this.isPaused = !1), this.isCleared = !1, this.animating || (this.animation = requestAnimationFrame(this.animateColors.bind(this)), this.animating = !0)
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function () {
        this.isPaused ? this.isPaused = !1 : cancelAnimationFrame(this.animation), this.isCleared = !0, this.context.clearRect(0, 0, this.x1, this.y1)
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function () {
        this.onResize("removeListeners"), this.onScroll("removeListeners"), this.clear()
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function () {
        var e = this;
        this.scrollDebounceTimeout && clearTimeout(this.scrollDebounceTimeout), this.scrollDebounceTimeout = setTimeout(function () {
            var t = e.canvas.getBoundingClientRect();
            if (e.isCanvasInWindowView = !(t.bottom < 0 || t.right < 0 || t.left > window.innerWidth || t.top > window.innerHeight), e.isCanvasInWindowView) {
                if (!e.isPaused || e.firstScrollInit) {
                    if (e.image && !e.isImgLoaded) return;
                    e.isPausedBecauseNotInView = !1, e.play("isPlayedBecauseInView"), e.firstScrollInit = !1
                }
            } else !e.image && e.firstScrollInit && (e.refreshColors(), e.firstScrollInit = !1), e.isPaused || e.isPausedBecauseNotInView || (e.isPausedBecauseNotInView = !0, e.pause("isPausedBecauseNotInView"))
        }, this.scrollDebounceThreshold)
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function () {
        this.getDimensions(), this.canvas.setAttribute("width", this.x1), this.canvas.setAttribute("height", this.y1), this.image && this.prepareImage(), this.refreshColors()
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function (e) {
        this.context.clearRect(0, 0, this.x1, this.y1), this.direction = e, this.validateInput("direction"), this.isPaused && this.refreshColors()
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function (e) {
        this.context.clearRect(0, 0, this.x1, this.y1), this.context.globalCompositeOperation = this.image.blendingMode = e, this.validateInput("blendingMode"), this.isPaused && this.refreshColors()
    }
}, function (e, t, n) {
    "use strict";
    e.exports = function (e) {
        var t, n, i = this;
        this.activeState !== e && (this.isPaused || (this.isPaused = !0, this.pause()), this.channelsIndex = -1, this.activetransitionSpeed = this.stateTransitionSpeed, this.activeColorDiff = [], this.activeColors = this.getCurrentColors(), this.progress = 0, this.previousTimeStamp = null, this.isChangingState = !0, this.states[e].gradients[0].forEach(function (o, r, s) {
            t = i.hexToRgb(i.states[e].gradients[0][r]), n = i.colorDiff(i.activeColors[r], t), i.activeColorDiff.push(n)
        }), this.activeState = e, this.play())
    }
}, function (e, t, n) {
    n(50), n(51), function (e) {
        page.registerVendor("Jarallax"), page.initJarallax = function () {
            e("[data-parallax]").each(function () {
                var t = e(this), n = {imgSrc: t.data("parallax"), speed: .3};
                t.hasClass("header") && (n.speed = .6), n = e.extend(n, page.getDataOptions(t)), t.jarallax(n)
            }), e("[data-video]").each(function () {
                var t = e(this), n = {videoSrc: t.data("video"), speed: 1};
                n.videoSrc.indexOf("mp4:") > -1 && (n.speed = .5), n = e.extend(n, page.getDataOptions(t)), t.jarallax(n)
            })
        }
    }(jQuery)
}, function (e, t) {
    /*!
 * Name    : Just Another Parallax [Jarallax]
 * Version : 1.10.3
 * Author  : nK <https://nkdev.info>
 * GitHub  : https://github.com/nk-o/jarallax
 */
    !function (e) {
        var t = {};

        function n(i) {
            if (t[i]) return t[i].exports;
            var o = t[i] = {i: i, l: !1, exports: {}};
            return e[i].call(o.exports, o, o.exports, n), o.l = !0, o.exports
        }

        n.m = e, n.c = t, n.d = function (e, t, i) {
            n.o(e, t) || Object.defineProperty(e, t, {configurable: !1, enumerable: !0, get: i})
        }, n.n = function (e) {
            var t = e && e.__esModule ? function () {
                return e.default
            } : function () {
                return e
            };
            return n.d(t, "a", t), t
        }, n.o = function (e, t) {
            return Object.prototype.hasOwnProperty.call(e, t)
        }, n.p = "", n(n.s = 11)
    }([function (e, t, n) {
        "use strict";
        (function (t) {
            var n;
            n = "undefined" != typeof window ? window : void 0 !== t ? t : "undefined" != typeof self ? self : {}, e.exports = n
        }).call(t, n(2))
    }, function (e, t, n) {
        "use strict";
        e.exports = function (e) {
            "complete" === document.readyState || "interactive" === document.readyState ? e.call() : document.attachEvent ? document.attachEvent("onreadystatechange", function () {
                "interactive" === document.readyState && e.call()
            }) : document.addEventListener && document.addEventListener("DOMContentLoaded", e)
        }
    }, function (e, t, n) {
        "use strict";
        var i, o = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (e) {
            return typeof e
        } : function (e) {
            return e && "function" == typeof Symbol && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e
        };
        i = function () {
            return this
        }();
        try {
            i = i || Function("return this")() || (0, eval)("this")
        } catch (e) {
            "object" === ("undefined" == typeof window ? "undefined" : o(window)) && (i = window)
        }
        e.exports = i
    }, , , , , , , , , function (e, t, n) {
        e.exports = n(12)
    }, function (e, t, n) {
        "use strict";
        var i = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (e) {
            return typeof e
        } : function (e) {
            return e && "function" == typeof Symbol && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e
        }, o = a(n(1)), r = n(0), s = a(n(13));

        function a(e) {
            return e && e.__esModule ? e : {default: e}
        }

        var l = r.window.jarallax;
        if (r.window.jarallax = s.default, r.window.jarallax.noConflict = function () {
            return r.window.jarallax = l, this
        }, void 0 !== r.jQuery) {
            var c = function () {
                var e = arguments || [];
                Array.prototype.unshift.call(e, this);
                var t = s.default.apply(r.window, e);
                return "object" !== (void 0 === t ? "undefined" : i(t)) ? t : this
            };
            c.constructor = s.default.constructor;
            var u = r.jQuery.fn.jarallax;
            r.jQuery.fn.jarallax = c, r.jQuery.fn.jarallax.noConflict = function () {
                return r.jQuery.fn.jarallax = u, this
            }
        }
        (0, o.default)(function () {
            (0, s.default)(document.querySelectorAll("[data-jarallax]"))
        })
    }, function (e, t, n) {
        "use strict";
        (function (e) {
            Object.defineProperty(t, "__esModule", {value: !0});
            var i = function (e, t) {
                if (Array.isArray(e)) return e;
                if (Symbol.iterator in Object(e)) return function (e, t) {
                    var n = [], i = !0, o = !1, r = void 0;
                    try {
                        for (var s, a = e[Symbol.iterator](); !(i = (s = a.next()).done) && (n.push(s.value), !t || n.length !== t); i = !0) ;
                    } catch (e) {
                        o = !0, r = e
                    } finally {
                        try {
                            !i && a.return && a.return()
                        } finally {
                            if (o) throw r
                        }
                    }
                    return n
                }(e, t);
                throw new TypeError("Invalid attempt to destructure non-iterable instance")
            }, o = function () {
                function e(e, t) {
                    for (var n = 0; n < t.length; n++) {
                        var i = t[n];
                        i.enumerable = i.enumerable || !1, i.configurable = !0, "value" in i && (i.writable = !0), Object.defineProperty(e, i.key, i)
                    }
                }

                return function (t, n, i) {
                    return n && e(t.prototype, n), i && e(t, i), t
                }
            }(), r = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (e) {
                return typeof e
            } : function (e) {
                return e && "function" == typeof Symbol && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e
            }, s = c(n(1)), a = c(n(14)), l = n(0);

            function c(e) {
                return e && e.__esModule ? e : {default: e}
            }

            var u = function () {
                for (var e = "transform WebkitTransform MozTransform".split(" "), t = document.createElement("div"), n = 0; n < e.length; n++) if (t && void 0 !== t.style[e[n]]) return e[n];
                return !1
            }(), d = void 0, p = void 0, f = void 0, h = !1, m = !1;

            function g(e) {
                d = l.window.innerWidth || document.documentElement.clientWidth, p = l.window.innerHeight || document.documentElement.clientHeight, "object" !== (void 0 === e ? "undefined" : r(e)) || "load" !== e.type && "dom-loaded" !== e.type || (h = !0)
            }

            g(), l.window.addEventListener("resize", g), l.window.addEventListener("orientationchange", g), l.window.addEventListener("load", g), (0, s.default)(function () {
                g({type: "dom-loaded"})
            });
            var v = [], y = !1;

            function b() {
                if (v.length) {
                    f = void 0 !== l.window.pageYOffset ? l.window.pageYOffset : (document.documentElement || document.body.parentNode || document.body).scrollTop;
                    var e = h || !y || y.width !== d || y.height !== p, t = m || e || !y || y.y !== f;
                    m = h = !1, (e || t) && (v.forEach(function (n) {
                        e && n.onResize(), t && n.onScroll()
                    }), y = {width: d, height: p, y: f}), (0, a.default)(b)
                }
            }

            var w = !!e.ResizeObserver && new e.ResizeObserver(function (e) {
                e && e.length && (0, a.default)(function () {
                    e.forEach(function (e) {
                        e.target && e.target.jarallax && (h || e.target.jarallax.onResize(), m = !0)
                    })
                })
            }), x = 0, T = function () {
                function e(t, n) {
                    !function (t, n) {
                        if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
                    }(this);
                    var o = this;
                    o.instanceID = x++, o.$item = t, o.defaults = {
                        type: "scroll",
                        speed: .5,
                        imgSrc: null,
                        imgElement: ".jarallax-img",
                        imgSize: "cover",
                        imgPosition: "50% 50%",
                        imgRepeat: "no-repeat",
                        keepImg: !1,
                        elementInViewport: null,
                        zIndex: -100,
                        disableParallax: !1,
                        disableVideo: !1,
                        automaticResize: !0,
                        videoSrc: null,
                        videoStartTime: 0,
                        videoEndTime: 0,
                        videoVolume: 0,
                        videoPlayOnlyVisible: !0,
                        onScroll: null,
                        onInit: null,
                        onDestroy: null,
                        onCoverImage: null
                    };
                    var s = o.$item.getAttribute("data-jarallax"), a = JSON.parse(s || "{}");
                    s && console.warn("Detected usage of deprecated data-jarallax JSON options, you should use pure data-attribute options. See info here - https://github.com/nk-o/jarallax/issues/53");
                    var l = o.$item.dataset || {}, c = {};
                    if (Object.keys(l).forEach(function (e) {
                        var t = e.substr(0, 1).toLowerCase() + e.substr(1);
                        t && void 0 !== o.defaults[t] && (c[t] = l[e])
                    }), o.options = o.extend({}, o.defaults, a, c, n), o.pureOptions = o.extend({}, o.options), Object.keys(o.options).forEach(function (e) {
                        "true" === o.options[e] ? o.options[e] = !0 : "false" === o.options[e] && (o.options[e] = !1)
                    }), o.options.speed = Math.min(2, Math.max(-1, parseFloat(o.options.speed))), (o.options.noAndroid || o.options.noIos) && (console.warn("Detected usage of deprecated noAndroid or noIos options, you should use disableParallax option. See info here - https://github.com/nk-o/jarallax/#disable-on-mobile-devices"), o.options.disableParallax || (o.options.noIos && o.options.noAndroid ? o.options.disableParallax = /iPad|iPhone|iPod|Android/ : o.options.noIos ? o.options.disableParallax = /iPad|iPhone|iPod/ : o.options.noAndroid && (o.options.disableParallax = /Android/))), "string" == typeof o.options.disableParallax && (o.options.disableParallax = new RegExp(o.options.disableParallax)), o.options.disableParallax instanceof RegExp) {
                        var u = o.options.disableParallax;
                        o.options.disableParallax = function () {
                            return u.test(navigator.userAgent)
                        }
                    }
                    if ("function" != typeof o.options.disableParallax && (o.options.disableParallax = function () {
                        return !1
                    }), "string" == typeof o.options.disableVideo && (o.options.disableVideo = new RegExp(o.options.disableVideo)), o.options.disableVideo instanceof RegExp) {
                        var d = o.options.disableVideo;
                        o.options.disableVideo = function () {
                            return d.test(navigator.userAgent)
                        }
                    }
                    "function" != typeof o.options.disableVideo && (o.options.disableVideo = function () {
                        return !1
                    });
                    var p = o.options.elementInViewport;
                    p && "object" === (void 0 === p ? "undefined" : r(p)) && void 0 !== p.length && (p = i(p, 1)[0]), p instanceof Element || (p = null), o.options.elementInViewport = p, o.image = {
                        src: o.options.imgSrc || null,
                        $container: null,
                        useImgTag: !1,
                        position: /iPad|iPhone|iPod|Android/.test(navigator.userAgent) ? "absolute" : "fixed"
                    }, o.initImg() && o.canInitParallax() && o.init()
                }

                return o(e, [{
                    key: "css", value: function (e, t) {
                        return "string" == typeof t ? l.window.getComputedStyle(e).getPropertyValue(t) : (t.transform && u && (t[u] = t.transform), Object.keys(t).forEach(function (n) {
                            e.style[n] = t[n]
                        }), e)
                    }
                }, {
                    key: "extend", value: function (e) {
                        var t = arguments;
                        return e = e || {}, Object.keys(arguments).forEach(function (n) {
                            t[n] && Object.keys(t[n]).forEach(function (i) {
                                e[i] = t[n][i]
                            })
                        }), e
                    }
                }, {
                    key: "getWindowData", value: function () {
                        return {width: d, height: p, y: f}
                    }
                }, {
                    key: "initImg", value: function () {
                        var e = this, t = e.options.imgElement;
                        return t && "string" == typeof t && (t = e.$item.querySelector(t)), t instanceof Element || (t = null), t && (e.options.keepImg ? e.image.$item = t.cloneNode(!0) : (e.image.$item = t, e.image.$itemParent = t.parentNode), e.image.useImgTag = !0), !(!e.image.$item && (null === e.image.src && (e.image.src = e.css(e.$item, "background-image").replace(/^url\(['"]?/g, "").replace(/['"]?\)$/g, "")), !e.image.src || "none" === e.image.src))
                    }
                }, {
                    key: "canInitParallax", value: function () {
                        return u && !this.options.disableParallax()
                    }
                }, {
                    key: "init", value: function () {
                        var e = this, t = {
                            position: "absolute",
                            top: 0,
                            left: 0,
                            width: "100%",
                            height: "100%",
                            overflow: "hidden",
                            pointerEvents: "none"
                        }, n = {};
                        if (!e.options.keepImg) {
                            var i = e.$item.getAttribute("style");
                            if (i && e.$item.setAttribute("data-jarallax-original-styles", i), e.image.useImgTag) {
                                var o = e.image.$item.getAttribute("style");
                                o && e.image.$item.setAttribute("data-jarallax-original-styles", o)
                            }
                        }
                        if ("static" === e.css(e.$item, "position") && e.css(e.$item, {position: "relative"}), "auto" === e.css(e.$item, "z-index") && e.css(e.$item, {zIndex: 0}), e.image.$container = document.createElement("div"), e.css(e.image.$container, t), e.css(e.image.$container, {"z-index": e.options.zIndex}), e.image.$container.setAttribute("id", "jarallax-container-" + e.instanceID), e.$item.appendChild(e.image.$container), e.image.useImgTag ? n = e.extend({
                            "object-fit": e.options.imgSize,
                            "object-position": e.options.imgPosition,
                            "font-family": "object-fit: " + e.options.imgSize + "; object-position: " + e.options.imgPosition + ";",
                            "max-width": "none"
                        }, t, n) : (e.image.$item = document.createElement("div"), e.image.src && (n = e.extend({
                            "background-position": e.options.imgPosition,
                            "background-size": e.options.imgSize,
                            "background-repeat": e.options.imgRepeat,
                            "background-image": 'url("' + e.image.src + '")'
                        }, t, n))), "opacity" !== e.options.type && "scale" !== e.options.type && "scale-opacity" !== e.options.type && 1 !== e.options.speed || (e.image.position = "absolute"), "fixed" === e.image.position) for (var r = 0, s = e.$item; null !== s && s !== document && 0 === r;) {
                            var a = e.css(s, "-webkit-transform") || e.css(s, "-moz-transform") || e.css(s, "transform");
                            a && "none" !== a && (r = 1, e.image.position = "absolute"), s = s.parentNode
                        }
                        n.position = e.image.position, e.css(e.image.$item, n), e.image.$container.appendChild(e.image.$item), e.coverImage(), e.clipContainer(), e.onScroll(!0), e.options.automaticResize && w && w.observe(e.$item), e.options.onInit && e.options.onInit.call(e), "none" !== e.css(e.$item, "background-image") && e.css(e.$item, {"background-image": "none"}), e.addToParallaxList()
                    }
                }, {
                    key: "addToParallaxList", value: function () {
                        v.push(this), 1 === v.length && b()
                    }
                }, {
                    key: "removeFromParallaxList", value: function () {
                        var e = this;
                        v.forEach(function (t, n) {
                            t.instanceID === e.instanceID && v.splice(n, 1)
                        })
                    }
                }, {
                    key: "destroy", value: function () {
                        var e = this;
                        e.removeFromParallaxList();
                        var t = e.$item.getAttribute("data-jarallax-original-styles");
                        if (e.$item.removeAttribute("data-jarallax-original-styles"), t ? e.$item.setAttribute("style", t) : e.$item.removeAttribute("style"), e.image.useImgTag) {
                            var n = e.image.$item.getAttribute("data-jarallax-original-styles");
                            e.image.$item.removeAttribute("data-jarallax-original-styles"), n ? e.image.$item.setAttribute("style", t) : e.image.$item.removeAttribute("style"), e.image.$itemParent && e.image.$itemParent.appendChild(e.image.$item)
                        }
                        e.$clipStyles && e.$clipStyles.parentNode.removeChild(e.$clipStyles), e.image.$container && e.image.$container.parentNode.removeChild(e.image.$container), e.options.onDestroy && e.options.onDestroy.call(e), delete e.$item.jarallax
                    }
                }, {
                    key: "clipContainer", value: function () {
                        if ("fixed" === this.image.position) {
                            var e = this, t = e.image.$container.getBoundingClientRect(), n = t.width, i = t.height;
                            e.$clipStyles || (e.$clipStyles = document.createElement("style"), e.$clipStyles.setAttribute("type", "text/css"), e.$clipStyles.setAttribute("id", "jarallax-clip-" + e.instanceID), (document.head || document.getElementsByTagName("head")[0]).appendChild(e.$clipStyles));
                            var o = "#jarallax-container-" + e.instanceID + " {\n           clip: rect(0 " + n + "px " + i + "px 0);\n           clip: rect(0, " + n + "px, " + i + "px, 0);\n        }";
                            e.$clipStyles.styleSheet ? e.$clipStyles.styleSheet.cssText = o : e.$clipStyles.innerHTML = o
                        }
                    }
                }, {
                    key: "coverImage", value: function () {
                        var e, t = this, n = t.image.$container.getBoundingClientRect(), i = n.height,
                            o = t.options.speed, r = "scroll" === t.options.type || "scroll-opacity" === t.options.type,
                            s = 0, a = i;
                        return r && (s = o < 0 ? o * Math.max(i, p) : o * (i + p), 1 < o ? a = Math.abs(s - p) : o < 0 ? a = s / o + Math.abs(s) : a += Math.abs(p - i) * (1 - o), s /= 2), t.parallaxScrollDistance = s, e = r ? (p - a) / 2 : (i - a) / 2, t.css(t.image.$item, {
                            height: a + "px",
                            marginTop: e + "px",
                            left: "fixed" === t.image.position ? n.left + "px" : "0",
                            width: n.width + "px"
                        }), t.options.onCoverImage && t.options.onCoverImage.call(t), {
                            image: {height: a, marginTop: e},
                            container: n
                        }
                    }
                }, {
                    key: "isVisible", value: function () {
                        return this.isElementInViewport || !1
                    }
                }, {
                    key: "onScroll", value: function (e) {
                        var t = this, n = t.$item.getBoundingClientRect(), i = n.top, o = n.height, r = {}, s = n;
                        if (t.options.elementInViewport && (s = t.options.elementInViewport.getBoundingClientRect()), t.isElementInViewport = 0 <= s.bottom && 0 <= s.right && s.top <= p && s.left <= d, e || t.isElementInViewport) {
                            var a = Math.max(0, i), l = Math.max(0, o + i), c = Math.max(0, -i),
                                u = Math.max(0, i + o - p), f = Math.max(0, o - (i + o - p)),
                                h = Math.max(0, -i + p - o), m = 1 - 2 * (p - i) / (p + o), g = 1;
                            if (o < p ? g = 1 - (c || u) / o : l <= p ? g = l / p : f <= p && (g = f / p), "opacity" !== t.options.type && "scale-opacity" !== t.options.type && "scroll-opacity" !== t.options.type || (r.transform = "translate3d(0,0,0)", r.opacity = g), "scale" === t.options.type || "scale-opacity" === t.options.type) {
                                var v = 1;
                                t.options.speed < 0 ? v -= t.options.speed * g : v += t.options.speed * (1 - g), r.transform = "scale(" + v + ") translate3d(0,0,0)"
                            }
                            if ("scroll" === t.options.type || "scroll-opacity" === t.options.type) {
                                var y = t.parallaxScrollDistance * m;
                                "absolute" === t.image.position && (y -= i), r.transform = "translate3d(0," + y + "px,0)"
                            }
                            t.css(t.image.$item, r), t.options.onScroll && t.options.onScroll.call(t, {
                                section: n,
                                beforeTop: a,
                                beforeTopEnd: l,
                                afterTop: c,
                                beforeBottom: u,
                                beforeBottomEnd: f,
                                afterBottom: h,
                                visiblePercent: g,
                                fromViewportCenter: m
                            })
                        }
                    }
                }, {
                    key: "onResize", value: function () {
                        this.coverImage(), this.clipContainer()
                    }
                }]), e
            }(), S = function (e) {
                ("object" === ("undefined" == typeof HTMLElement ? "undefined" : r(HTMLElement)) ? e instanceof HTMLElement : e && "object" === (void 0 === e ? "undefined" : r(e)) && null !== e && 1 === e.nodeType && "string" == typeof e.nodeName) && (e = [e]);
                for (var t = arguments[1], n = Array.prototype.slice.call(arguments, 2), i = e.length, o = 0, s = void 0; o < i; o++) if ("object" === (void 0 === t ? "undefined" : r(t)) || void 0 === t ? e[o].jarallax || (e[o].jarallax = new T(e[o], t)) : e[o].jarallax && (s = e[o].jarallax[t].apply(e[o].jarallax, n)), void 0 !== s) return s;
                return e
            };
            S.constructor = T, t.default = S
        }).call(t, n(2))
    }, function (e, t, n) {
        "use strict";
        var i = n(0),
            o = i.requestAnimationFrame || i.webkitRequestAnimationFrame || i.mozRequestAnimationFrame || function (e) {
                var t = +new Date, n = Math.max(0, 16 - (t - r)), i = setTimeout(e, n);
                return r = t, i
            }, r = +new Date,
            s = i.cancelAnimationFrame || i.webkitCancelAnimationFrame || i.mozCancelAnimationFrame || clearTimeout;
        Function.prototype.bind && (o = o.bind(i), s = s.bind(i)), (e.exports = o).cancel = s
    }])
}, function (e, t) {
    /*!
 * Name    : Video Background Extension for Jarallax
 * Version : 1.0.1
 * Author  : nK <https://nkdev.info>
 * GitHub  : https://github.com/nk-o/jarallax
 */
    !function (e) {
        var t = {};

        function n(i) {
            if (t[i]) return t[i].exports;
            var o = t[i] = {i: i, l: !1, exports: {}};
            return e[i].call(o.exports, o, o.exports, n), o.l = !0, o.exports
        }

        n.m = e, n.c = t, n.d = function (e, t, i) {
            n.o(e, t) || Object.defineProperty(e, t, {configurable: !1, enumerable: !0, get: i})
        }, n.n = function (e) {
            var t = e && e.__esModule ? function () {
                return e.default
            } : function () {
                return e
            };
            return n.d(t, "a", t), t
        }, n.o = function (e, t) {
            return Object.prototype.hasOwnProperty.call(e, t)
        }, n.p = "", n(n.s = 7)
    }([function (e, t, n) {
        "use strict";
        (function (t) {
            var n;
            n = "undefined" != typeof window ? window : void 0 !== t ? t : "undefined" != typeof self ? self : {}, e.exports = n
        }).call(t, n(2))
    }, function (e, t, n) {
        "use strict";
        e.exports = function (e) {
            "complete" === document.readyState || "interactive" === document.readyState ? e.call() : document.attachEvent ? document.attachEvent("onreadystatechange", function () {
                "interactive" === document.readyState && e.call()
            }) : document.addEventListener && document.addEventListener("DOMContentLoaded", e)
        }
    }, function (e, t, n) {
        "use strict";
        var i, o = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (e) {
            return typeof e
        } : function (e) {
            return e && "function" == typeof Symbol && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e
        };
        i = function () {
            return this
        }();
        try {
            i = i || Function("return this")() || (0, eval)("this")
        } catch (e) {
            "object" === ("undefined" == typeof window ? "undefined" : o(window)) && (i = window)
        }
        e.exports = i
    }, function (e, t, n) {
        "use strict";
        e.exports = n(9)
    }, , , , function (e, t, n) {
        e.exports = n(8)
    }, function (e, t, n) {
        "use strict";
        var i = a(n(3)), o = a(n(0)), r = a(n(1)), s = a(n(10));

        function a(e) {
            return e && e.__esModule ? e : {default: e}
        }

        o.default.VideoWorker = o.default.VideoWorker || i.default, (0, s.default)(), (0, r.default)(function () {
            "undefined" != typeof jarallax && jarallax(document.querySelectorAll("[data-jarallax-video]"))
        })
    }, function (e, t, n) {
        "use strict";
        Object.defineProperty(t, "__esModule", {value: !0});
        var i = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (e) {
            return typeof e
        } : function (e) {
            return e && "function" == typeof Symbol && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e
        }, o = function () {
            function e(e, t) {
                for (var n = 0; n < t.length; n++) {
                    var i = t[n];
                    i.enumerable = i.enumerable || !1, i.configurable = !0, "value" in i && (i.writable = !0), Object.defineProperty(e, i.key, i)
                }
            }

            return function (t, n, i) {
                return n && e(t.prototype, n), i && e(t, i), t
            }
        }();

        function r() {
            this._done = [], this._fail = []
        }

        r.prototype = {
            execute: function (e, t) {
                var n = e.length;
                for (t = Array.prototype.slice.call(t); n--;) e[n].apply(null, t)
            }, resolve: function () {
                this.execute(this._done, arguments)
            }, reject: function () {
                this.execute(this._fail, arguments)
            }, done: function (e) {
                this._done.push(e)
            }, fail: function (e) {
                this._fail.push(e)
            }
        };
        var s = 0, a = 0, l = 0, c = 0, u = 0, d = new r, p = new r, f = function () {
            function e(t, n) {
                !function (t, n) {
                    if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
                }(this);
                var i = this;
                i.url = t, i.options_default = {
                    autoplay: !1,
                    loop: !1,
                    mute: !1,
                    volume: 100,
                    showContols: !0,
                    startTime: 0,
                    endTime: 0
                }, i.options = i.extend({}, i.options_default, n), i.videoID = i.parseURL(t), i.videoID && (i.ID = s++, i.loadAPI(), i.init())
            }

            return o(e, [{
                key: "extend", value: function (e) {
                    var t = arguments;
                    return e = e || {}, Object.keys(arguments).forEach(function (n) {
                        t[n] && Object.keys(t[n]).forEach(function (i) {
                            e[i] = t[n][i]
                        })
                    }), e
                }
            }, {
                key: "parseURL", value: function (e) {
                    var t, n, i, o, r,
                        s = !(!(t = e.match(/.*(?:youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=)([^#\&\?]*).*/)) || 11 !== t[1].length) && t[1],
                        a = !(!(n = e.match(/https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/)) || !n[3]) && n[3],
                        l = (i = e.split(/,(?=mp4\:|webm\:|ogv\:|ogg\:)/), o = {}, r = 0, i.forEach(function (e) {
                            var t = e.match(/^(mp4|webm|ogv|ogg)\:(.*)/);
                            t && t[1] && t[2] && (o["ogv" === t[1] ? "ogg" : t[1]] = t[2], r = 1)
                        }), !!r && o);
                    return s ? (this.type = "youtube", s) : a ? (this.type = "vimeo", a) : !!l && (this.type = "local", l)
                }
            }, {
                key: "isValid", value: function () {
                    return !!this.videoID
                }
            }, {
                key: "on", value: function (e, t) {
                    this.userEventsList = this.userEventsList || [], (this.userEventsList[e] || (this.userEventsList[e] = [])).push(t)
                }
            }, {
                key: "off", value: function (e, t) {
                    var n = this;
                    this.userEventsList && this.userEventsList[e] && (t ? this.userEventsList[e].forEach(function (i, o) {
                        i === t && (n.userEventsList[e][o] = !1)
                    }) : delete this.userEventsList[e])
                }
            }, {
                key: "fire", value: function (e) {
                    var t = this, n = [].slice.call(arguments, 1);
                    this.userEventsList && void 0 !== this.userEventsList[e] && this.userEventsList[e].forEach(function (e) {
                        e && e.apply(t, n)
                    })
                }
            }, {
                key: "play", value: function (e) {
                    var t = this;
                    t.player && ("youtube" === t.type && t.player.playVideo && (void 0 !== e && t.player.seekTo(e || 0), YT.PlayerState.PLAYING !== t.player.getPlayerState() && t.player.playVideo()), "vimeo" === t.type && (void 0 !== e && t.player.setCurrentTime(e), t.player.getPaused().then(function (e) {
                        e && t.player.play()
                    })), "local" === t.type && (void 0 !== e && (t.player.currentTime = e), t.player.paused && t.player.play()))
                }
            }, {
                key: "pause", value: function () {
                    var e = this;
                    e.player && ("youtube" === e.type && e.player.pauseVideo && YT.PlayerState.PLAYING === e.player.getPlayerState() && e.player.pauseVideo(), "vimeo" === e.type && e.player.getPaused().then(function (t) {
                        t || e.player.pause()
                    }), "local" === e.type && (e.player.paused || e.player.pause()))
                }
            }, {
                key: "mute", value: function () {
                    var e = this;
                    e.player && ("youtube" === e.type && e.player.mute && e.player.mute(), "vimeo" === e.type && e.player.setVolume && e.player.setVolume(0), "local" === e.type && (e.$video.muted = !0))
                }
            }, {
                key: "unmute", value: function () {
                    var e = this;
                    e.player && ("youtube" === e.type && e.player.mute && e.player.unMute(), "vimeo" === e.type && e.player.setVolume && e.player.setVolume(e.options.volume), "local" === e.type && (e.$video.muted = !1))
                }
            }, {
                key: "setVolume", value: function () {
                    var e = 0 < arguments.length && void 0 !== arguments[0] && arguments[0], t = this;
                    t.player && e && ("youtube" === t.type && t.player.setVolume && t.player.setVolume(e), "vimeo" === t.type && t.player.setVolume && t.player.setVolume(e), "local" === t.type && (t.$video.volume = e / 100))
                }
            }, {
                key: "getVolume", value: function (e) {
                    var t = this;
                    t.player ? ("youtube" === t.type && t.player.getVolume && e(t.player.getVolume()), "vimeo" === t.type && t.player.getVolume && t.player.getVolume().then(function (t) {
                        e(t)
                    }), "local" === t.type && e(100 * t.$video.volume)) : e(!1)
                }
            }, {
                key: "getMuted", value: function (e) {
                    var t = this;
                    t.player ? ("youtube" === t.type && t.player.isMuted && e(t.player.isMuted()), "vimeo" === t.type && t.player.getVolume && t.player.getVolume().then(function (t) {
                        e(!!t)
                    }), "local" === t.type && e(t.$video.muted)) : e(null)
                }
            }, {
                key: "getImageURL", value: function (e) {
                    var t = this;
                    if (t.videoImage) e(t.videoImage); else {
                        if ("youtube" === t.type) {
                            var n = ["maxresdefault", "sddefault", "hqdefault", "0"], i = 0, o = new Image;
                            o.onload = function () {
                                120 !== (this.naturalWidth || this.width) || i === n.length - 1 ? (t.videoImage = "https://img.youtube.com/vi/" + t.videoID + "/" + n[i] + ".jpg", e(t.videoImage)) : (i++, this.src = "https://img.youtube.com/vi/" + t.videoID + "/" + n[i] + ".jpg")
                            }, o.src = "https://img.youtube.com/vi/" + t.videoID + "/" + n[i] + ".jpg"
                        }
                        if ("vimeo" === t.type) {
                            var r = new XMLHttpRequest;
                            r.open("GET", "https://vimeo.com/api/v2/video/" + t.videoID + ".json", !0), r.onreadystatechange = function () {
                                if (4 === this.readyState && 200 <= this.status && this.status < 400) {
                                    var n = JSON.parse(this.responseText);
                                    t.videoImage = n[0].thumbnail_large, e(t.videoImage)
                                }
                            }, r.send(), r = null
                        }
                    }
                }
            }, {
                key: "getIframe", value: function (e) {
                    this.getVideo(e)
                }
            }, {
                key: "getVideo", value: function (e) {
                    var t = this;
                    t.$video ? e(t.$video) : t.onAPIready(function () {
                        var n = void 0;
                        if (t.$video || ((n = document.createElement("div")).style.display = "none"), "youtube" === t.type) {
                            t.playerOptions = {}, t.playerOptions.videoId = t.videoID, t.playerOptions.playerVars = {
                                autohide: 1,
                                rel: 0,
                                autoplay: 0,
                                playsinline: 1
                            }, t.options.showContols || (t.playerOptions.playerVars.iv_load_policy = 3, t.playerOptions.playerVars.modestbranding = 1, t.playerOptions.playerVars.controls = 0, t.playerOptions.playerVars.showinfo = 0, t.playerOptions.playerVars.disablekb = 1);
                            var i = void 0, o = void 0;
                            t.playerOptions.events = {
                                onReady: function (e) {
                                    t.options.mute ? e.target.mute() : t.options.volume && e.target.setVolume(t.options.volume), t.options.autoplay && t.play(t.options.startTime), t.fire("ready", e), setInterval(function () {
                                        t.getVolume(function (n) {
                                            t.options.volume !== n && (t.options.volume = n, t.fire("volumechange", e))
                                        })
                                    }, 150)
                                }, onStateChange: function (e) {
                                    t.options.loop && e.data === YT.PlayerState.ENDED && t.play(t.options.startTime), i || e.data !== YT.PlayerState.PLAYING || (i = 1, t.fire("started", e)), e.data === YT.PlayerState.PLAYING && t.fire("play", e), e.data === YT.PlayerState.PAUSED && t.fire("pause", e), e.data === YT.PlayerState.ENDED && t.fire("ended", e), e.data === YT.PlayerState.PLAYING ? o = setInterval(function () {
                                        t.fire("timeupdate", e), t.options.endTime && t.player.getCurrentTime() >= t.options.endTime && (t.options.loop ? t.play(t.options.startTime) : t.pause())
                                    }, 150) : clearInterval(o)
                                }
                            };
                            var r = !t.$video;
                            if (r) {
                                var s = document.createElement("div");
                                s.setAttribute("id", t.playerID), n.appendChild(s), document.body.appendChild(n)
                            }
                            t.player = t.player || new window.YT.Player(t.playerID, t.playerOptions), r && (t.$video = document.getElementById(t.playerID), t.videoWidth = parseInt(t.$video.getAttribute("width"), 10) || 1280, t.videoHeight = parseInt(t.$video.getAttribute("height"), 10) || 720)
                        }
                        if ("vimeo" === t.type) {
                            t.playerOptions = "", t.playerOptions += "player_id=" + t.playerID, t.playerOptions += "&autopause=0", t.playerOptions += "&transparent=0", t.options.showContols || (t.playerOptions += "&badge=0&byline=0&portrait=0&title=0"), t.playerOptions += "&autoplay=" + (t.options.autoplay ? "1" : "0"), t.playerOptions += "&loop=" + (t.options.loop ? 1 : 0), t.$video || (t.$video = document.createElement("iframe"), t.$video.setAttribute("id", t.playerID), t.$video.setAttribute("src", "https://player.vimeo.com/video/" + t.videoID + "?" + t.playerOptions), t.$video.setAttribute("frameborder", "0"), n.appendChild(t.$video), document.body.appendChild(n)), t.player = t.player || new Vimeo.Player(t.$video), t.player.getVideoWidth().then(function (e) {
                                t.videoWidth = e || 1280
                            }), t.player.getVideoHeight().then(function (e) {
                                t.videoHeight = e || 720
                            }), t.options.startTime && t.options.autoplay && t.player.setCurrentTime(t.options.startTime), t.options.mute ? t.player.setVolume(0) : t.options.volume && t.player.setVolume(t.options.volume);
                            var a = void 0;
                            t.player.on("timeupdate", function (e) {
                                a || (t.fire("started", e), a = 1), t.fire("timeupdate", e), t.options.endTime && t.options.endTime && e.seconds >= t.options.endTime && (t.options.loop ? t.play(t.options.startTime) : t.pause())
                            }), t.player.on("play", function (e) {
                                t.fire("play", e), t.options.startTime && 0 === e.seconds && t.play(t.options.startTime)
                            }), t.player.on("pause", function (e) {
                                t.fire("pause", e)
                            }), t.player.on("ended", function (e) {
                                t.fire("ended", e)
                            }), t.player.on("loaded", function (e) {
                                t.fire("ready", e)
                            }), t.player.on("volumechange", function (e) {
                                t.fire("volumechange", e)
                            })
                        }
                        if ("local" === t.type) {
                            t.$video || (t.$video = document.createElement("video"), t.options.mute ? t.$video.muted = !0 : t.$video.volume && (t.$video.volume = t.options.volume / 100), t.options.loop && (t.$video.loop = !0), t.$video.setAttribute("playsinline", ""), t.$video.setAttribute("webkit-playsinline", ""), t.$video.setAttribute("id", t.playerID), n.appendChild(t.$video), document.body.appendChild(n), Object.keys(t.videoID).forEach(function (e) {
                                var n, i, o, r;
                                n = t.$video, i = t.videoID[e], o = "video/" + e, (r = document.createElement("source")).src = i, r.type = o, n.appendChild(r)
                            })), t.player = t.player || t.$video;
                            var l = void 0;
                            t.player.addEventListener("playing", function (e) {
                                l || t.fire("started", e), l = 1
                            }), t.player.addEventListener("timeupdate", function (e) {
                                t.fire("timeupdate", e), t.options.endTime && t.options.endTime && this.currentTime >= t.options.endTime && (t.options.loop ? t.play(t.options.startTime) : t.pause())
                            }), t.player.addEventListener("play", function (e) {
                                t.fire("play", e)
                            }), t.player.addEventListener("pause", function (e) {
                                t.fire("pause", e)
                            }), t.player.addEventListener("ended", function (e) {
                                t.fire("ended", e)
                            }), t.player.addEventListener("loadedmetadata", function () {
                                t.videoWidth = this.videoWidth || 1280, t.videoHeight = this.videoHeight || 720, t.fire("ready"), t.options.autoplay && t.play(t.options.startTime)
                            }), t.player.addEventListener("volumechange", function (e) {
                                t.getVolume(function (e) {
                                    t.options.volume = e
                                }), t.fire("volumechange", e)
                            })
                        }
                        e(t.$video)
                    })
                }
            }, {
                key: "init", value: function () {
                    this.playerID = "VideoWorker-" + this.ID
                }
            }, {
                key: "loadAPI", value: function () {
                    if (!a || !l) {
                        var e = "";
                        if ("youtube" !== this.type || a || (a = 1, e = "https://www.youtube.com/iframe_api"), "vimeo" !== this.type || l || (l = 1, e = "https://player.vimeo.com/api/player.js"), e) {
                            var t = document.createElement("script"), n = document.getElementsByTagName("head")[0];
                            t.src = e, n.appendChild(t), t = n = null
                        }
                    }
                }
            }, {
                key: "onAPIready", value: function (e) {
                    if ("youtube" === this.type && ("undefined" != typeof YT && 0 !== YT.loaded || c ? "object" === ("undefined" == typeof YT ? "undefined" : i(YT)) && 1 === YT.loaded ? e() : d.done(function () {
                        e()
                    }) : (c = 1, window.onYouTubeIframeAPIReady = function () {
                        window.onYouTubeIframeAPIReady = null, d.resolve("done"), e()
                    })), "vimeo" === this.type) if ("undefined" != typeof Vimeo || u) "undefined" != typeof Vimeo ? e() : p.done(function () {
                        e()
                    }); else {
                        u = 1;
                        var t = setInterval(function () {
                            "undefined" != typeof Vimeo && (clearInterval(t), p.resolve("done"), e())
                        }, 20)
                    }
                    "local" === this.type && e()
                }
            }]), e
        }();
        t.default = f
    }, function (e, t, n) {
        "use strict";
        Object.defineProperty(t, "__esModule", {value: !0}), t.default = function () {
            var e = 0 < arguments.length && void 0 !== arguments[0] ? arguments[0] : o.default.jarallax;
            if (void 0 !== e) {
                var t = e.constructor, n = t.prototype.init;
                t.prototype.init = function () {
                    var e = this;
                    n.apply(e), e.video && !e.options.disableVideo() && e.video.getVideo(function (t) {
                        var n = t.parentNode;
                        e.css(t, {
                            position: e.image.position,
                            top: "0px",
                            left: "0px",
                            right: "0px",
                            bottom: "0px",
                            width: "100%",
                            height: "100%",
                            maxWidth: "none",
                            maxHeight: "none",
                            margin: 0,
                            zIndex: -1
                        }), e.$video = t, e.image.$container.appendChild(t), n.parentNode.removeChild(n)
                    })
                };
                var r = t.prototype.coverImage;
                t.prototype.coverImage = function () {
                    var e = this, t = r.apply(e), n = !!e.image.$item && e.image.$item.nodeName;
                    if (t && e.video && n && ("IFRAME" === n || "VIDEO" === n)) {
                        var i = t.image.height, o = i * e.image.width / e.image.height, s = (t.container.width - o) / 2,
                            a = t.image.marginTop;
                        t.container.width > o && (i = (o = t.container.width) * e.image.height / e.image.width, s = 0, a += (t.image.height - i) / 2), "IFRAME" === n && (i += 400, a -= 200), e.css(e.$video, {
                            width: o + "px",
                            marginLeft: s + "px",
                            height: i + "px",
                            marginTop: a + "px"
                        })
                    }
                    return t
                };
                var s = t.prototype.initImg;
                t.prototype.initImg = function () {
                    var e = this, t = s.apply(e);
                    return e.options.videoSrc || (e.options.videoSrc = e.$item.getAttribute("data-jarallax-video") || null), e.options.videoSrc ? (e.defaultInitImgResult = t, !0) : t
                };
                var a = t.prototype.canInitParallax;
                t.prototype.canInitParallax = function () {
                    var e = this, t = a.apply(e);
                    if (!e.options.videoSrc) return t;
                    var n = new i.default(e.options.videoSrc, {
                        autoplay: !0,
                        loop: !0,
                        showContols: !1,
                        startTime: e.options.videoStartTime || 0,
                        endTime: e.options.videoEndTime || 0,
                        mute: e.options.videoVolume ? 0 : 1,
                        volume: e.options.videoVolume || 0
                    });
                    if (n.isValid()) if (t) {
                        if (n.on("ready", function () {
                            if (e.options.videoPlayOnlyVisible) {
                                var t = e.onScroll;
                                e.onScroll = function () {
                                    t.apply(e), e.isVisible() ? n.play() : n.pause()
                                }
                            } else n.play()
                        }), n.on("started", function () {
                            e.image.$default_item = e.image.$item, e.image.$item = e.$video, e.image.width = e.video.videoWidth || 1280, e.image.height = e.video.videoHeight || 720, e.options.imgWidth = e.image.width, e.options.imgHeight = e.image.height, e.coverImage(), e.clipContainer(), e.onScroll(), e.image.$default_item && (e.image.$default_item.style.display = "none")
                        }), e.video = n, !e.defaultInitImgResult) return "local" !== n.type ? (n.getImageURL(function (t) {
                            e.image.src = t, e.init()
                        }), !1) : (e.image.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7", !0)
                    } else e.defaultInitImgResult || n.getImageURL(function (t) {
                        var n = e.$item.getAttribute("style");
                        n && e.$item.setAttribute("data-jarallax-original-styles", n), e.css(e.$item, {
                            "background-image": 'url("' + t + '")',
                            "background-position": "center",
                            "background-size": "cover"
                        })
                    });
                    return t
                };
                var l = t.prototype.destroy;
                t.prototype.destroy = function () {
                    var e = this;
                    e.image.$default_item && (e.image.$item = e.image.$default_item, delete e.image.$default_item), l.apply(e)
                }
            }
        };
        var i = r(n(3)), o = r(n(0));

        function r(e) {
            return e && e.__esModule ? e : {default: e}
        }
    }])
}, function (e, t, n) {
    window.Lity = n(53), function (e) {
        page.registerVendor("Lity"), page.initLity = function () {
            e(document).on("click", '[data-provide~="lightbox"]', Lity)
        }
    }(jQuery)
}, function (e, t, n) {
    var i, o;
    /*! Lity - v2.3.1 - 2018-04-20
* http://sorgalla.com/lity/
* Copyright (c) 2015-2018 Jan Sorgalla; Licensed MIT */
    /*! Lity - v2.3.1 - 2018-04-20
* http://sorgalla.com/lity/
* Copyright (c) 2015-2018 Jan Sorgalla; Licensed MIT */
    !function (r, s) {
        i = [n(3)], void 0 === (o = function (e) {
            return function (e, t) {
                "use strict";
                var n = e.document, i = t(e), o = t.Deferred, r = t("html"), s = [], a = "aria-hidden", l = "lity-" + a,
                    c = 'a[href],area[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),button:not([disabled]),iframe,object,embed,[contenteditable],[tabindex]:not([tabindex^="-"])',
                    u = {
                        esc: !0,
                        handler: null,
                        handlers: {
                            image: T, inline: function (e, n) {
                                var i, o, r;
                                try {
                                    i = t(e)
                                } catch (e) {
                                    return !1
                                }
                                if (!i.length) return !1;
                                return o = t('<i style="display:none !important"/>'), r = i.hasClass("lity-hide"), n.element().one("lity:remove", function () {
                                    o.before(i).remove(), r && !i.closest(".lity-content").length && i.addClass("lity-hide")
                                }), i.removeClass("lity-hide").after(o)
                            }, youtube: function (e) {
                                var n = p.exec(e);
                                if (!n) return !1;
                                return S(x(e, w("https://www.youtube" + (n[2] || "") + ".com/embed/" + n[4], t.extend({autoplay: 1}, b(n[5] || "")))))
                            }, vimeo: function (e) {
                                var n = f.exec(e);
                                if (!n) return !1;
                                return S(x(e, w("https://player.vimeo.com/video/" + n[3], t.extend({autoplay: 1}, b(n[4] || "")))))
                            }, googlemaps: function (e) {
                                var t = h.exec(e);
                                if (!t) return !1;
                                return S(x(e, w("https://www.google." + t[3] + "/maps?" + t[6], {output: t[6].indexOf("layer=c") > 0 ? "svembed" : "embed"})))
                            }, facebookvideo: function (e) {
                                var n = m.exec(e);
                                if (!n) return !1;
                                0 !== e.indexOf("http") && (e = "https:" + e);
                                return S(x(e, w("https://www.facebook.com/plugins/video.php?href=" + e, t.extend({autoplay: 1}, b(n[4] || "")))))
                            }, iframe: S
                        },
                        template: '<div class="lity" role="dialog" aria-label="Dialog Window (Press escape to close)" tabindex="-1"><div class="lity-wrap" data-lity-close role="document"><div class="lity-loader" aria-hidden="true">Loading...</div><div class="lity-container"><div class="lity-content"></div><button class="lity-close" type="button" aria-label="Close (Press escape to close)" data-lity-close>&times;</button></div></div></div>'
                    }, d = /(^data:image\/)|(\.(png|jpe?g|gif|svg|webp|bmp|ico|tiff?)(\?\S*)?$)/i,
                    p = /(youtube(-nocookie)?\.com|youtu\.be)\/(watch\?v=|v\/|u\/|embed\/?)?([\w-]{11})(.*)?/i,
                    f = /(vimeo(pro)?.com)\/(?:[^\d]+)?(\d+)\??(.*)?$/,
                    h = /((maps|www)\.)?google\.([^\/\?]+)\/?((maps\/?)?\?)(.*)/i,
                    m = /(facebook\.com)\/([a-z0-9_-]*)\/videos\/([0-9]*)(.*)?$/i, g = function () {
                        var e = n.createElement("div"), t = {
                            WebkitTransition: "webkitTransitionEnd",
                            MozTransition: "transitionend",
                            OTransition: "oTransitionEnd otransitionend",
                            transition: "transitionend"
                        };
                        for (var i in t) if (void 0 !== e.style[i]) return t[i];
                        return !1
                    }();

                function v(e) {
                    var t = o();
                    return g && e.length ? (e.one(g, t.resolve), setTimeout(t.resolve, 500)) : t.resolve(), t.promise()
                }

                function y(e, n, i) {
                    if (1 === arguments.length) return t.extend({}, e);
                    if ("string" == typeof n) {
                        if (void 0 === i) return void 0 === e[n] ? null : e[n];
                        e[n] = i
                    } else t.extend(e, n);
                    return this
                }

                function b(e) {
                    for (var t, n = decodeURI(e.split("#")[0]).split("&"), i = {}, o = 0, r = n.length; o < r; o++) n[o] && (t = n[o].split("="), i[t[0]] = t[1]);
                    return i
                }

                function w(e, n) {
                    return e + (e.indexOf("?") > -1 ? "&" : "?") + t.param(n)
                }

                function x(e, t) {
                    var n = e.indexOf("#");
                    return -1 === n ? t : (n > 0 && (e = e.substr(n)), t + e)
                }

                function T(e, n) {
                    var i = n.opener() && n.opener().data("lity-desc") || "Image with no description",
                        r = t('<img src="' + e + '" alt="' + i + '"/>'), s = o(), a = function () {
                            s.reject(function (e) {
                                return t('<span class="lity-error"/>').append(e)
                            }("Failed loading image"))
                        };
                    return r.on("load", function () {
                        if (0 === this.naturalWidth) return a();
                        s.resolve(r)
                    }).on("error", a), s.promise()
                }

                function S(e) {
                    return '<div class="lity-iframe-container"><iframe frameborder="0" allowfullscreen src="' + e + '"/></div>'
                }

                function C() {
                    return n.documentElement.clientHeight ? n.documentElement.clientHeight : Math.round(i.height())
                }

                function E(e) {
                    var t = _();
                    t && (27 === e.keyCode && t.options("esc") && t.close(), 9 === e.keyCode && function (e, t) {
                        var i = t.element().find(c), o = i.index(n.activeElement);
                        e.shiftKey && o <= 0 ? (i.get(i.length - 1).focus(), e.preventDefault()) : e.shiftKey || o !== i.length - 1 || (i.get(0).focus(), e.preventDefault())
                    }(e, t))
                }

                function k() {
                    t.each(s, function (e, t) {
                        t.resize()
                    })
                }

                function _() {
                    return 0 === s.length ? null : s[0]
                }

                function A(e, c, d, p) {
                    var f, h, m, g = this, b = !1, w = !1;
                    c = t.extend({}, u, c), h = t(c.template), g.element = function () {
                        return h
                    }, g.opener = function () {
                        return d
                    }, g.options = t.proxy(y, g, c), g.handlers = t.proxy(y, g, c.handlers), g.resize = function () {
                        b && !w && m.css("max-height", C() + "px").trigger("lity:resize", [g])
                    }, g.close = function () {
                        if (b && !w) {
                            w = !0, function (e) {
                                var n;
                                e.element().attr(a, "true"), 1 === s.length && (r.removeClass("lity-active"), i.off({
                                    resize: k,
                                    keydown: E
                                }));
                                n = (s = t.grep(s, function (t) {
                                    return e !== t
                                })).length ? s[0].element() : t(".lity-hidden");
                                n.removeClass("lity-hidden").each(function () {
                                    var e = t(this), n = e.data(l);
                                    n ? e.attr(a, n) : e.removeAttr(a), e.removeData(l)
                                })
                            }(g);
                            var e = o();
                            if (p && (n.activeElement === h[0] || t.contains(h[0], n.activeElement))) try {
                                p.focus()
                            } catch (e) {
                            }
                            return m.trigger("lity:close", [g]), h.removeClass("lity-opened").addClass("lity-closed"), v(m.add(h)).always(function () {
                                m.trigger("lity:remove", [g]), h.remove(), h = void 0, e.resolve()
                            }), e.promise()
                        }
                    }, f = function (e, n, i, o) {
                        var r, s = "inline", a = t.extend({}, i);
                        o && a[o] ? (r = a[o](e, n), s = o) : (t.each(["inline", "iframe"], function (e, t) {
                            delete a[t], a[t] = i[t]
                        }), t.each(a, function (t, i) {
                            return !i || (!(!i.test || i.test(e, n)) || (!1 !== (r = i(e, n)) ? (s = t, !1) : void 0))
                        }));
                        return {handler: s, content: r || ""}
                    }(e, g, c.handlers, c.handler), h.attr(a, "false").addClass("lity-loading lity-opened lity-" + f.handler).appendTo("body").focus().on("click", "[data-lity-close]", function (e) {
                        t(e.target).is("[data-lity-close]") && g.close()
                    }).trigger("lity:open", [g]), function (e) {
                        1 === s.unshift(e) && (r.addClass("lity-active"), i.on({resize: k, keydown: E}));
                        t("body > *").not(e.element()).addClass("lity-hidden").each(function () {
                            var e = t(this);
                            void 0 === e.data(l) && e.data(l, e.attr(a) || null)
                        }).attr(a, "true")
                    }(g), t.when(f.content).always(function (e) {
                        m = t(e).css("max-height", C() + "px"), h.find(".lity-loader").each(function () {
                            var e = t(this);
                            v(e).always(function () {
                                e.remove()
                            })
                        }), h.removeClass("lity-loading").find(".lity-content").empty().append(m), b = !0, m.trigger("lity:ready", [g])
                    })
                }

                function I(e, i, o) {
                    e.preventDefault ? (e.preventDefault(), o = t(this), e = o.data("lity-target") || o.attr("href") || o.attr("src")) : o = t(o);
                    var r = new A(e, t.extend({}, o.data("lity-options") || o.data("lity"), i), o, n.activeElement);
                    if (!e.preventDefault) return r
                }

                return T.test = function (e) {
                    return d.test(e)
                }, I.version = "2.3.1", I.options = t.proxy(y, I, u), I.handlers = t.proxy(y, I, u.handlers), I.current = _, t(n).on("click.lity", "[data-lity]", I), I
            }(r, e)
        }.apply(t, i)) || (e.exports = o)
    }("undefined" != typeof window ? window : this)
}, function (e, t, n) {
    n(55), function (e) {
        page.registerVendor("PhotoSwipe"), page.initPhotoSwipe = function () {
            e('[data-provide="photoswipe"]').each(function () {
                var t = e(this), n = t.dataAttr("photoswipe-selector", ".gallery-item");
                e(t).photoSwipe(n)
            })
        }
    }(jQuery)
}, function (e, t, n) {
    var i, o, r;
    !function e(t, n, o) {
        function r(a, l) {
            if (!n[a]) {
                if (!t[a]) {
                    if (!l && ("function" == typeof i && i)) return i(a, !0);
                    if (s) return s(a, !0);
                    var c = new Error("Cannot find module '" + a + "'");
                    throw c.code = "MODULE_NOT_FOUND", c
                }
                var u = n[a] = {exports: {}};
                t[a][0].call(u.exports, function (e) {
                    var n = t[a][1][e];
                    return r(n || e)
                }, u, u.exports, e, t, n, o)
            }
            return n[a].exports
        }

        for (var s = "function" == typeof i && i, a = 0; a < o.length; a++) r(o[a]);
        return r
    }({
        1: [function (e, t, i) {
            /*! PhotoSwipe - v4.1.1 - 2015-12-24
* http://photoswipe.com
* Copyright (c) 2015 Dmitry Semenov; */
            void 0 === (r = "function" == typeof (o = function () {
                "use strict";
                return function (e, t, n, i) {
                    var o = {
                        features: null, bind: function (e, t, n, i) {
                            var o = (i ? "remove" : "add") + "EventListener";
                            t = t.split(" ");
                            for (var r = 0; r < t.length; r++) t[r] && e[o](t[r], n, !1)
                        }, isArray: function (e) {
                            return e instanceof Array
                        }, createEl: function (e, t) {
                            var n = document.createElement(t || "div");
                            return e && (n.className = e), n
                        }, getScrollY: function () {
                            var e = window.pageYOffset;
                            return void 0 !== e ? e : document.documentElement.scrollTop
                        }, unbind: function (e, t, n) {
                            o.bind(e, t, n, !0)
                        }, removeClass: function (e, t) {
                            var n = new RegExp("(\\s|^)" + t + "(\\s|$)");
                            e.className = e.className.replace(n, " ").replace(/^\s\s*/, "").replace(/\s\s*$/, "")
                        }, addClass: function (e, t) {
                            o.hasClass(e, t) || (e.className += (e.className ? " " : "") + t)
                        }, hasClass: function (e, t) {
                            return e.className && new RegExp("(^|\\s)" + t + "(\\s|$)").test(e.className)
                        }, getChildByClass: function (e, t) {
                            for (var n = e.firstChild; n;) {
                                if (o.hasClass(n, t)) return n;
                                n = n.nextSibling
                            }
                        }, arraySearch: function (e, t, n) {
                            for (var i = e.length; i--;) if (e[i][n] === t) return i;
                            return -1
                        }, extend: function (e, t, n) {
                            for (var i in t) if (t.hasOwnProperty(i)) {
                                if (n && e.hasOwnProperty(i)) continue;
                                e[i] = t[i]
                            }
                        }, easing: {
                            sine: {
                                out: function (e) {
                                    return Math.sin(e * (Math.PI / 2))
                                }, inOut: function (e) {
                                    return -(Math.cos(Math.PI * e) - 1) / 2
                                }
                            }, cubic: {
                                out: function (e) {
                                    return --e * e * e + 1
                                }
                            }
                        }, detectFeatures: function () {
                            if (o.features) return o.features;
                            var e = o.createEl(), t = e.style, n = "", i = {};
                            if (i.oldIE = document.all && !document.addEventListener, i.touch = "ontouchstart" in window, window.requestAnimationFrame && (i.raf = window.requestAnimationFrame, i.caf = window.cancelAnimationFrame), i.pointerEvent = navigator.pointerEnabled || navigator.msPointerEnabled, !i.pointerEvent) {
                                var r = navigator.userAgent;
                                if (/iP(hone|od)/.test(navigator.platform)) {
                                    var s = navigator.appVersion.match(/OS (\d+)_(\d+)_?(\d+)?/);
                                    s && s.length > 0 && (s = parseInt(s[1], 10)) >= 1 && s < 8 && (i.isOldIOSPhone = !0)
                                }
                                var a = r.match(/Android\s([0-9\.]*)/), l = a ? a[1] : 0;
                                (l = parseFloat(l)) >= 1 && (l < 4.4 && (i.isOldAndroid = !0), i.androidVersion = l), i.isMobileOpera = /opera mini|opera mobi/i.test(r)
                            }
                            for (var c, u, d = ["transform", "perspective", "animationName"], p = ["", "webkit", "Moz", "ms", "O"], f = 0; f < 4; f++) {
                                n = p[f];
                                for (var h = 0; h < 3; h++) c = d[h], u = n + (n ? c.charAt(0).toUpperCase() + c.slice(1) : c), !i[c] && u in t && (i[c] = u);
                                n && !i.raf && (n = n.toLowerCase(), i.raf = window[n + "RequestAnimationFrame"], i.raf && (i.caf = window[n + "CancelAnimationFrame"] || window[n + "CancelRequestAnimationFrame"]))
                            }
                            if (!i.raf) {
                                var m = 0;
                                i.raf = function (e) {
                                    var t = (new Date).getTime(), n = Math.max(0, 16 - (t - m)),
                                        i = window.setTimeout(function () {
                                            e(t + n)
                                        }, n);
                                    return m = t + n, i
                                }, i.caf = function (e) {
                                    clearTimeout(e)
                                }
                            }
                            return i.svg = !!document.createElementNS && !!document.createElementNS("http://www.w3.org/2000/svg", "svg").createSVGRect, o.features = i, i
                        }
                    };
                    o.detectFeatures(), o.features.oldIE && (o.bind = function (e, t, n, i) {
                        t = t.split(" ");
                        for (var o, r = (i ? "detach" : "attach") + "Event", s = function () {
                            n.handleEvent.call(n)
                        }, a = 0; a < t.length; a++) if (o = t[a]) if ("object" == typeof n && n.handleEvent) {
                            if (i) {
                                if (!n["oldIE" + o]) return !1
                            } else n["oldIE" + o] = s;
                            e[r]("on" + o, n["oldIE" + o])
                        } else e[r]("on" + o, n)
                    });
                    var r = this, s = {
                        allowPanToNext: !0,
                        spacing: .12,
                        bgOpacity: 1,
                        mouseUsed: !1,
                        loop: !0,
                        pinchToClose: !0,
                        closeOnScroll: !0,
                        closeOnVerticalDrag: !0,
                        verticalDragRange: .75,
                        hideAnimationDuration: 333,
                        showAnimationDuration: 333,
                        showHideOpacity: !1,
                        focus: !0,
                        escKey: !0,
                        arrowKeys: !0,
                        mainScrollEndFriction: .35,
                        panEndFriction: .35,
                        isClickableElement: function (e) {
                            return "A" === e.tagName
                        },
                        getDoubleTapZoom: function (e, t) {
                            return e ? 1 : t.initialZoomLevel < .7 ? 1 : 1.33
                        },
                        maxSpreadZoom: 1.33,
                        modal: !0,
                        scaleMode: "fit"
                    };
                    o.extend(s, i);
                    var a, l, c, u, d, p, f, h, m, g, v, y, b, w, x, T, S, C, E, k, _, A, I, O, D, L, N, P, M, j, H, $,
                        F, R, W, V, z, q, B, U, G, Y, K, Q, Z, X, J, ee, te, ne, ie, oe, re, se, ae, le = {x: 0, y: 0},
                        ce = {x: 0, y: 0}, ue = {x: 0, y: 0}, de = {}, pe = 0, fe = {}, he = {x: 0, y: 0}, me = 0,
                        ge = !0, ve = [], ye = {}, be = !1, we = function (e, t) {
                            o.extend(r, t.publicMethods), ve.push(e)
                        }, xe = function (e) {
                            var t = Rt();
                            return e > t - 1 ? e - t : e < 0 ? t + e : e
                        }, Te = {}, Se = function (e, t) {
                            return Te[e] || (Te[e] = []), Te[e].push(t)
                        }, Ce = function (e) {
                            var t = Te[e];
                            if (t) {
                                var n = Array.prototype.slice.call(arguments);
                                n.shift();
                                for (var i = 0; i < t.length; i++) t[i].apply(r, n)
                            }
                        }, Ee = function () {
                            return (new Date).getTime()
                        }, ke = function (e) {
                            re = e, r.bg.style.opacity = e * s.bgOpacity
                        }, _e = function (e, t, n, i, o) {
                            (!be || o && o !== r.currItem) && (i /= o ? o.fitRatio : r.currItem.fitRatio), e[A] = y + t + "px, " + n + "px" + b + " scale(" + i + ")"
                        }, Ae = function (e) {
                            ee && (e && (g > r.currItem.fitRatio ? be || (Kt(r.currItem, !1, !0), be = !0) : be && (Kt(r.currItem), be = !1)), _e(ee, ue.x, ue.y, g))
                        }, Ie = function (e) {
                            e.container && _e(e.container.style, e.initialPosition.x, e.initialPosition.y, e.initialZoomLevel, e)
                        }, Oe = function (e, t) {
                            t[A] = y + e + "px, 0px" + b
                        }, De = function (e, t) {
                            if (!s.loop && t) {
                                var n = u + (he.x * pe - e) / he.x, i = Math.round(e - ct.x);
                                (n < 0 && i > 0 || n >= Rt() - 1 && i < 0) && (e = ct.x + i * s.mainScrollEndFriction)
                            }
                            ct.x = e, Oe(e, d)
                        }, Le = function (e, t) {
                            var n = ut[e] - fe[e];
                            return ce[e] + le[e] + n - n * (t / v)
                        }, Ne = function (e, t) {
                            e.x = t.x, e.y = t.y, t.id && (e.id = t.id)
                        }, Pe = function (e) {
                            e.x = Math.round(e.x), e.y = Math.round(e.y)
                        }, Me = null, je = function () {
                            Me && (o.unbind(document, "mousemove", je), o.addClass(e, "pswp--has_mouse"), s.mouseUsed = !0, Ce("mouseUsed")), Me = setTimeout(function () {
                                Me = null
                            }, 100)
                        }, He = function (e, t) {
                            var n = Bt(r.currItem, de, e);
                            return t && (J = n), n
                        }, $e = function (e) {
                            return e || (e = r.currItem), e.initialZoomLevel
                        }, Fe = function (e) {
                            return e || (e = r.currItem), e.w > 0 ? s.maxSpreadZoom : 1
                        }, Re = function (e, t, n, i) {
                            return i === r.currItem.initialZoomLevel ? (n[e] = r.currItem.initialPosition[e], !0) : (n[e] = Le(e, i), n[e] > t.min[e] ? (n[e] = t.min[e], !0) : n[e] < t.max[e] && (n[e] = t.max[e], !0))
                        }, We = function (e) {
                            var t = "";
                            s.escKey && 27 === e.keyCode ? t = "close" : s.arrowKeys && (37 === e.keyCode ? t = "prev" : 39 === e.keyCode && (t = "next")), t && (e.ctrlKey || e.altKey || e.shiftKey || e.metaKey || (e.preventDefault ? e.preventDefault() : e.returnValue = !1, r[t]()))
                        }, Ve = function (e) {
                            e && (G || U || te || V) && (e.preventDefault(), e.stopPropagation())
                        }, ze = function () {
                            r.setScrollOffset(0, o.getScrollY())
                        }, qe = {}, Be = 0, Ue = function (e) {
                            qe[e] && (qe[e].raf && L(qe[e].raf), Be--, delete qe[e])
                        }, Ge = function (e) {
                            qe[e] && Ue(e), qe[e] || (Be++, qe[e] = {})
                        }, Ye = function () {
                            for (var e in qe) qe.hasOwnProperty(e) && Ue(e)
                        }, Ke = function (e, t, n, i, o, r, s) {
                            var a, l = Ee();
                            Ge(e);
                            var c = function () {
                                if (qe[e]) {
                                    if ((a = Ee() - l) >= i) return Ue(e), r(n), void (s && s());
                                    r((n - t) * o(a / i) + t), qe[e].raf = D(c)
                                }
                            };
                            c()
                        }, Qe = {
                            shout: Ce, listen: Se, viewportSize: de, options: s, isMainScrollAnimating: function () {
                                return te
                            }, getZoomLevel: function () {
                                return g
                            }, getCurrentIndex: function () {
                                return u
                            }, isDragging: function () {
                                return q
                            }, isZooming: function () {
                                return Z
                            }, setScrollOffset: function (e, t) {
                                fe.x = e, j = fe.y = t, Ce("updateScrollOffset", fe)
                            }, applyZoomPan: function (e, t, n, i) {
                                ue.x = t, ue.y = n, g = e, Ae(i)
                            }, init: function () {
                                if (!a && !l) {
                                    var n;
                                    r.framework = o, r.template = e, r.bg = o.getChildByClass(e, "pswp__bg"), N = e.className, a = !0, H = o.detectFeatures(), D = H.raf, L = H.caf, A = H.transform, M = H.oldIE, r.scrollWrap = o.getChildByClass(e, "pswp__scroll-wrap"), r.container = o.getChildByClass(r.scrollWrap, "pswp__container"), d = r.container.style, r.itemHolders = T = [{
                                        el: r.container.children[0],
                                        wrap: 0,
                                        index: -1
                                    }, {el: r.container.children[1], wrap: 0, index: -1}, {
                                        el: r.container.children[2],
                                        wrap: 0,
                                        index: -1
                                    }], T[0].el.style.display = T[2].el.style.display = "none", function () {
                                        if (A) {
                                            var t = H.perspective && !O;
                                            return y = "translate" + (t ? "3d(" : "("), void (b = H.perspective ? ", 0px)" : ")")
                                        }
                                        A = "left", o.addClass(e, "pswp--ie"), Oe = function (e, t) {
                                            t.left = e + "px"
                                        }, Ie = function (e) {
                                            var t = e.fitRatio > 1 ? 1 : e.fitRatio, n = e.container.style, i = t * e.w,
                                                o = t * e.h;
                                            n.width = i + "px", n.height = o + "px", n.left = e.initialPosition.x + "px", n.top = e.initialPosition.y + "px"
                                        }, Ae = function () {
                                            if (ee) {
                                                var e = ee, t = r.currItem, n = t.fitRatio > 1 ? 1 : t.fitRatio,
                                                    i = n * t.w, o = n * t.h;
                                                e.width = i + "px", e.height = o + "px", e.left = ue.x + "px", e.top = ue.y + "px"
                                            }
                                        }
                                    }(), m = {resize: r.updateSize, scroll: ze, keydown: We, click: Ve};
                                    var i = H.isOldIOSPhone || H.isOldAndroid || H.isMobileOpera;
                                    for (H.animationName && H.transform && !i || (s.showAnimationDuration = s.hideAnimationDuration = 0), n = 0; n < ve.length; n++) r["init" + ve[n]]();
                                    if (t) {
                                        var c = r.ui = new t(r, o);
                                        c.init()
                                    }
                                    Ce("firstUpdate"), u = u || s.index || 0, (isNaN(u) || u < 0 || u >= Rt()) && (u = 0), r.currItem = Ft(u), (H.isOldIOSPhone || H.isOldAndroid) && (ge = !1), e.setAttribute("aria-hidden", "false"), s.modal && (ge ? e.style.position = "fixed" : (e.style.position = "absolute", e.style.top = o.getScrollY() + "px")), void 0 === j && (Ce("initialLayout"), j = P = o.getScrollY());
                                    var f = "pswp--open ";
                                    for (s.mainClass && (f += s.mainClass + " "), s.showHideOpacity && (f += "pswp--animate_opacity "), f += O ? "pswp--touch" : "pswp--notouch", f += H.animationName ? " pswp--css_animation" : "", f += H.svg ? " pswp--svg" : "", o.addClass(e, f), r.updateSize(), p = -1, me = null, n = 0; n < 3; n++) Oe((n + p) * he.x, T[n].el.style);
                                    M || o.bind(r.scrollWrap, h, r), Se("initialZoomInEnd", function () {
                                        r.setContent(T[0], u - 1), r.setContent(T[2], u + 1), T[0].el.style.display = T[2].el.style.display = "block", s.focus && e.focus(), o.bind(document, "keydown", r), H.transform && o.bind(r.scrollWrap, "click", r), s.mouseUsed || o.bind(document, "mousemove", je), o.bind(window, "resize scroll", r), Ce("bindEvents")
                                    }), r.setContent(T[1], u), r.updateCurrItem(), Ce("afterInit"), ge || (w = setInterval(function () {
                                        Be || q || Z || g !== r.currItem.initialZoomLevel || r.updateSize()
                                    }, 1e3)), o.addClass(e, "pswp--visible")
                                }
                            }, close: function () {
                                a && (a = !1, l = !0, Ce("close"), o.unbind(window, "resize", r), o.unbind(window, "scroll", m.scroll), o.unbind(document, "keydown", r), o.unbind(document, "mousemove", je), H.transform && o.unbind(r.scrollWrap, "click", r), q && o.unbind(window, f, r), Ce("unbindEvents"), Wt(r.currItem, null, !0, r.destroy))
                            }, destroy: function () {
                                Ce("destroy"), Mt && clearTimeout(Mt), e.setAttribute("aria-hidden", "true"), e.className = N, w && clearInterval(w), o.unbind(r.scrollWrap, h, r), o.unbind(window, "scroll", r), ft(), Ye(), Te = null
                            }, panTo: function (e, t, n) {
                                n || (e > J.min.x ? e = J.min.x : e < J.max.x && (e = J.max.x), t > J.min.y ? t = J.min.y : t < J.max.y && (t = J.max.y)), ue.x = e, ue.y = t, Ae()
                            }, handleEvent: function (e) {
                                e = e || window.event, m[e.type] && m[e.type](e)
                            }, goTo: function (e) {
                                var t = (e = xe(e)) - u;
                                me = t, u = e, r.currItem = Ft(u), pe -= t, De(he.x * pe), Ye(), te = !1, r.updateCurrItem()
                            }, next: function () {
                                r.goTo(u + 1)
                            }, prev: function () {
                                r.goTo(u - 1)
                            }, updateCurrZoomItem: function (e) {
                                if (e && Ce("beforeChange", 0), T[1].el.children.length) {
                                    var t = T[1].el.children[0];
                                    ee = o.hasClass(t, "pswp__zoom-wrap") ? t.style : null
                                } else ee = null;
                                J = r.currItem.bounds, v = g = r.currItem.initialZoomLevel, ue.x = J.center.x, ue.y = J.center.y, e && Ce("afterChange")
                            }, invalidateCurrItems: function () {
                                x = !0;
                                for (var e = 0; e < 3; e++) T[e].item && (T[e].item.needsUpdate = !0)
                            }, updateCurrItem: function (e) {
                                if (0 !== me) {
                                    var t, n = Math.abs(me);
                                    if (!(e && n < 2)) {
                                        r.currItem = Ft(u), be = !1, Ce("beforeChange", me), n >= 3 && (p += me + (me > 0 ? -3 : 3), n = 3);
                                        for (var i = 0; i < n; i++) me > 0 ? (t = T.shift(), T[2] = t, Oe((++p + 2) * he.x, t.el.style), r.setContent(t, u - n + i + 1 + 1)) : (t = T.pop(), T.unshift(t), Oe(--p * he.x, t.el.style), r.setContent(t, u + n - i - 1 - 1));
                                        if (ee && 1 === Math.abs(me)) {
                                            var o = Ft(S);
                                            o.initialZoomLevel !== g && (Bt(o, de), Kt(o), Ie(o))
                                        }
                                        me = 0, r.updateCurrZoomItem(), S = u, Ce("afterChange")
                                    }
                                }
                            }, updateSize: function (t) {
                                if (!ge && s.modal) {
                                    var n = o.getScrollY();
                                    if (j !== n && (e.style.top = n + "px", j = n), !t && ye.x === window.innerWidth && ye.y === window.innerHeight) return;
                                    ye.x = window.innerWidth, ye.y = window.innerHeight, e.style.height = ye.y + "px"
                                }
                                if (de.x = r.scrollWrap.clientWidth, de.y = r.scrollWrap.clientHeight, ze(), he.x = de.x + Math.round(de.x * s.spacing), he.y = de.y, De(he.x * pe), Ce("beforeResize"), void 0 !== p) {
                                    for (var i, a, l, c = 0; c < 3; c++) i = T[c], Oe((c + p) * he.x, i.el.style), l = u + c - 1, s.loop && Rt() > 2 && (l = xe(l)), (a = Ft(l)) && (x || a.needsUpdate || !a.bounds) ? (r.cleanSlide(a), r.setContent(i, l), 1 === c && (r.currItem = a, r.updateCurrZoomItem(!0)), a.needsUpdate = !1) : -1 === i.index && l >= 0 && r.setContent(i, l), a && a.container && (Bt(a, de), Kt(a), Ie(a));
                                    x = !1
                                }
                                v = g = r.currItem.initialZoomLevel, (J = r.currItem.bounds) && (ue.x = J.center.x, ue.y = J.center.y, Ae(!0)), Ce("resize")
                            }, zoomTo: function (e, t, n, i, r) {
                                t && (v = g, ut.x = Math.abs(t.x) - ue.x, ut.y = Math.abs(t.y) - ue.y, Ne(ce, ue));
                                var s = He(e, !1), a = {};
                                Re("x", s, a, e), Re("y", s, a, e);
                                var l = g, c = {x: ue.x, y: ue.y};
                                Pe(a);
                                var u = function (t) {
                                    1 === t ? (g = e, ue.x = a.x, ue.y = a.y) : (g = (e - l) * t + l, ue.x = (a.x - c.x) * t + c.x, ue.y = (a.y - c.y) * t + c.y), r && r(t), Ae(1 === t)
                                };
                                n ? Ke("customZoomTo", 0, 1, n, i || o.easing.sine.inOut, u) : u(1)
                            }
                        }, Ze = {}, Xe = {}, Je = {}, et = {}, tt = {}, nt = [], it = {}, ot = [], rt = {}, st = 0,
                        at = {x: 0, y: 0}, lt = 0, ct = {x: 0, y: 0}, ut = {x: 0, y: 0}, dt = {x: 0, y: 0},
                        pt = function (e, t) {
                            return rt.x = Math.abs(e.x - t.x), rt.y = Math.abs(e.y - t.y), Math.sqrt(rt.x * rt.x + rt.y * rt.y)
                        }, ft = function () {
                            Y && (L(Y), Y = null)
                        }, ht = function () {
                            q && (Y = D(ht), At())
                        }, mt = function (e, t) {
                            return !(!e || e === document) && !(e.getAttribute("class") && e.getAttribute("class").indexOf("pswp__scroll-wrap") > -1) && (t(e) ? e : mt(e.parentNode, t))
                        }, gt = {}, vt = function (e, t) {
                            return gt.prevent = !mt(e.target, s.isClickableElement), Ce("preventDragEvent", e, t, gt), gt.prevent
                        }, yt = function (e, t) {
                            return t.x = e.pageX, t.y = e.pageY, t.id = e.identifier, t
                        }, bt = function (e, t, n) {
                            n.x = .5 * (e.x + t.x), n.y = .5 * (e.y + t.y)
                        }, wt = function () {
                            var e = ue.y - r.currItem.initialPosition.y;
                            return 1 - Math.abs(e / (de.y / 2))
                        }, xt = {}, Tt = {}, St = [], Ct = function (e) {
                            for (; St.length > 0;) St.pop();
                            return I ? (ae = 0, nt.forEach(function (e) {
                                0 === ae ? St[0] = e : 1 === ae && (St[1] = e), ae++
                            })) : e.type.indexOf("touch") > -1 ? e.touches && e.touches.length > 0 && (St[0] = yt(e.touches[0], xt), e.touches.length > 1 && (St[1] = yt(e.touches[1], Tt))) : (xt.x = e.pageX, xt.y = e.pageY, xt.id = "", St[0] = xt), St
                        }, Et = function (e, t) {
                            var n, i, o, a, l = ue[e] + t[e], c = t[e] > 0, u = ct.x + t.x, d = ct.x - it.x;
                            if (n = l > J.min[e] || l < J.max[e] ? s.panEndFriction : 1, l = ue[e] + t[e] * n, (s.allowPanToNext || g === r.currItem.initialZoomLevel) && (ee ? "h" !== ne || "x" !== e || U || (c ? (l > J.min[e] && (n = s.panEndFriction, J.min[e], i = J.min[e] - ce[e]), (i <= 0 || d < 0) && Rt() > 1 ? (a = u, d < 0 && u > it.x && (a = it.x)) : J.min.x !== J.max.x && (o = l)) : (l < J.max[e] && (n = s.panEndFriction, J.max[e], i = ce[e] - J.max[e]), (i <= 0 || d > 0) && Rt() > 1 ? (a = u, d > 0 && u < it.x && (a = it.x)) : J.min.x !== J.max.x && (o = l))) : a = u, "x" === e)) return void 0 !== a && (De(a, !0), K = a !== it.x), J.min.x !== J.max.x && (void 0 !== o ? ue.x = o : K || (ue.x += t.x * n)), void 0 !== a;
                            te || K || g > r.currItem.fitRatio && (ue[e] += t[e] * n)
                        }, kt = function (e) {
                            if (!("mousedown" === e.type && e.button > 0)) if ($t) e.preventDefault(); else if (!z || "mousedown" !== e.type) {
                                if (vt(e, !0) && e.preventDefault(), Ce("pointerDown"), I) {
                                    var t = o.arraySearch(nt, e.pointerId, "id");
                                    t < 0 && (t = nt.length), nt[t] = {x: e.pageX, y: e.pageY, id: e.pointerId}
                                }
                                var n = Ct(e), i = n.length;
                                Q = null, Ye(), q && 1 !== i || (q = ie = !0, o.bind(window, f, r), W = se = oe = V = K = G = B = U = !1, ne = null, Ce("firstTouchStart", n), Ne(ce, ue), le.x = le.y = 0, Ne(et, n[0]), Ne(tt, et), it.x = he.x * pe, ot = [{
                                    x: et.x,
                                    y: et.y
                                }], F = $ = Ee(), He(g, !0), ft(), ht()), !Z && i > 1 && !te && !K && (v = g, U = !1, Z = B = !0, le.y = le.x = 0, Ne(ce, ue), Ne(Ze, n[0]), Ne(Xe, n[1]), bt(Ze, Xe, dt), ut.x = Math.abs(dt.x) - ue.x, ut.y = Math.abs(dt.y) - ue.y, X = pt(Ze, Xe))
                            }
                        }, _t = function (e) {
                            if (e.preventDefault(), I) {
                                var t = o.arraySearch(nt, e.pointerId, "id");
                                if (t > -1) {
                                    var n = nt[t];
                                    n.x = e.pageX, n.y = e.pageY
                                }
                            }
                            if (q) {
                                var i = Ct(e);
                                if (ne || G || Z) Q = i; else if (ct.x !== he.x * pe) ne = "h"; else {
                                    var r = Math.abs(i[0].x - et.x) - Math.abs(i[0].y - et.y);
                                    Math.abs(r) >= 10 && (ne = r > 0 ? "h" : "v", Q = i)
                                }
                            }
                        }, At = function () {
                            if (Q) {
                                var e = Q.length;
                                if (0 !== e) if (Ne(Ze, Q[0]), Je.x = Ze.x - et.x, Je.y = Ze.y - et.y, Z && e > 1) {
                                    if (et.x = Ze.x, et.y = Ze.y, !Je.x && !Je.y && function (e, t) {
                                        return e.x === t.x && e.y === t.y
                                    }(Q[1], Xe)) return;
                                    Ne(Xe, Q[1]), U || (U = !0, Ce("zoomGestureStarted"));
                                    var t = pt(Ze, Xe), n = Nt(t);
                                    n > r.currItem.initialZoomLevel + r.currItem.initialZoomLevel / 15 && (se = !0);
                                    var i = 1, o = $e(), a = Fe();
                                    if (n < o) if (s.pinchToClose && !se && v <= r.currItem.initialZoomLevel) {
                                        var l = o - n, c = 1 - l / (o / 1.2);
                                        ke(c), Ce("onPinchClose", c), oe = !0
                                    } else (i = (o - n) / o) > 1 && (i = 1), n = o - i * (o / 3); else n > a && ((i = (n - a) / (6 * o)) > 1 && (i = 1), n = a + i * o);
                                    i < 0 && (i = 0), bt(Ze, Xe, at), le.x += at.x - dt.x, le.y += at.y - dt.y, Ne(dt, at), ue.x = Le("x", n), ue.y = Le("y", n), W = n > g, g = n, Ae()
                                } else {
                                    if (!ne) return;
                                    if (ie && (ie = !1, Math.abs(Je.x) >= 10 && (Je.x -= Q[0].x - tt.x), Math.abs(Je.y) >= 10 && (Je.y -= Q[0].y - tt.y)), et.x = Ze.x, et.y = Ze.y, 0 === Je.x && 0 === Je.y) return;
                                    if ("v" === ne && s.closeOnVerticalDrag && "fit" === s.scaleMode && g === r.currItem.initialZoomLevel) {
                                        le.y += Je.y, ue.y += Je.y;
                                        var u = wt();
                                        return V = !0, Ce("onVerticalDrag", u), ke(u), void Ae()
                                    }
                                    !function (e, t, n) {
                                        if (e - F > 50) {
                                            var i = ot.length > 2 ? ot.shift() : {};
                                            i.x = t, i.y = n, ot.push(i), F = e
                                        }
                                    }(Ee(), Ze.x, Ze.y), G = !0, J = r.currItem.bounds;
                                    var d = Et("x", Je);
                                    d || (Et("y", Je), Pe(ue), Ae())
                                }
                            }
                        }, It = function (e) {
                            if (H.isOldAndroid) {
                                if (z && "mouseup" === e.type) return;
                                e.type.indexOf("touch") > -1 && (clearTimeout(z), z = setTimeout(function () {
                                    z = 0
                                }, 600))
                            }
                            var t;
                            if (Ce("pointerUp"), vt(e, !1) && e.preventDefault(), I) {
                                var n = o.arraySearch(nt, e.pointerId, "id");
                                n > -1 && (t = nt.splice(n, 1)[0], navigator.pointerEnabled ? t.type = e.pointerType || "mouse" : (t.type = {
                                    4: "mouse",
                                    2: "touch",
                                    3: "pen"
                                }[e.pointerType], t.type || (t.type = e.pointerType || "mouse")))
                            }
                            var i, a = Ct(e), l = a.length;
                            if ("mouseup" === e.type && (l = 0), 2 === l) return Q = null, !0;
                            1 === l && Ne(tt, a[0]), 0 !== l || ne || te || (t || ("mouseup" === e.type ? t = {
                                x: e.pageX,
                                y: e.pageY,
                                type: "mouse"
                            } : e.changedTouches && e.changedTouches[0] && (t = {
                                x: e.changedTouches[0].pageX,
                                y: e.changedTouches[0].pageY,
                                type: "touch"
                            })), Ce("touchRelease", e, t));
                            var c = -1;
                            if (0 === l && (q = !1, o.unbind(window, f, r), ft(), Z ? c = 0 : -1 !== lt && (c = Ee() - lt)), lt = 1 === l ? Ee() : -1, i = -1 !== c && c < 150 ? "zoom" : "swipe", Z && l < 2 && (Z = !1, 1 === l && (i = "zoomPointerUp"), Ce("zoomGestureEnded")), Q = null, G || U || te || V) if (Ye(), R || (R = Ot()), R.calculateSwipeSpeed("x"), V) {
                                var u = wt();
                                if (u < s.verticalDragRange) r.close(); else {
                                    var d = ue.y, p = re;
                                    Ke("verticalDrag", 0, 1, 300, o.easing.cubic.out, function (e) {
                                        ue.y = (r.currItem.initialPosition.y - d) * e + d, ke((1 - p) * e + p), Ae()
                                    }), Ce("onVerticalDrag", 1)
                                }
                            } else {
                                if ((K || te) && 0 === l) {
                                    var h = Lt(i, R);
                                    if (h) return;
                                    i = "zoomPointerUp"
                                }
                                te || ("swipe" === i ? !K && g > r.currItem.fitRatio && Dt(R) : Pt())
                            }
                        }, Ot = function () {
                            var e, t, n = {
                                lastFlickOffset: {},
                                lastFlickDist: {},
                                lastFlickSpeed: {},
                                slowDownRatio: {},
                                slowDownRatioReverse: {},
                                speedDecelerationRatio: {},
                                speedDecelerationRatioAbs: {},
                                distanceOffset: {},
                                backAnimDestination: {},
                                backAnimStarted: {},
                                calculateSwipeSpeed: function (i) {
                                    ot.length > 1 ? (e = Ee() - F + 50, t = ot[ot.length - 2][i]) : (e = Ee() - $, t = tt[i]), n.lastFlickOffset[i] = et[i] - t, n.lastFlickDist[i] = Math.abs(n.lastFlickOffset[i]), n.lastFlickDist[i] > 20 ? n.lastFlickSpeed[i] = n.lastFlickOffset[i] / e : n.lastFlickSpeed[i] = 0, Math.abs(n.lastFlickSpeed[i]) < .1 && (n.lastFlickSpeed[i] = 0), n.slowDownRatio[i] = .95, n.slowDownRatioReverse[i] = 1 - n.slowDownRatio[i], n.speedDecelerationRatio[i] = 1
                                },
                                calculateOverBoundsAnimOffset: function (e, t) {
                                    n.backAnimStarted[e] || (ue[e] > J.min[e] ? n.backAnimDestination[e] = J.min[e] : ue[e] < J.max[e] && (n.backAnimDestination[e] = J.max[e]), void 0 !== n.backAnimDestination[e] && (n.slowDownRatio[e] = .7, n.slowDownRatioReverse[e] = 1 - n.slowDownRatio[e], n.speedDecelerationRatioAbs[e] < .05 && (n.lastFlickSpeed[e] = 0, n.backAnimStarted[e] = !0, Ke("bounceZoomPan" + e, ue[e], n.backAnimDestination[e], t || 300, o.easing.sine.out, function (t) {
                                        ue[e] = t, Ae()
                                    }))))
                                },
                                calculateAnimOffset: function (e) {
                                    n.backAnimStarted[e] || (n.speedDecelerationRatio[e] = n.speedDecelerationRatio[e] * (n.slowDownRatio[e] + n.slowDownRatioReverse[e] - n.slowDownRatioReverse[e] * n.timeDiff / 10), n.speedDecelerationRatioAbs[e] = Math.abs(n.lastFlickSpeed[e] * n.speedDecelerationRatio[e]), n.distanceOffset[e] = n.lastFlickSpeed[e] * n.speedDecelerationRatio[e] * n.timeDiff, ue[e] += n.distanceOffset[e])
                                },
                                panAnimLoop: function () {
                                    if (qe.zoomPan && (qe.zoomPan.raf = D(n.panAnimLoop), n.now = Ee(), n.timeDiff = n.now - n.lastNow, n.lastNow = n.now, n.calculateAnimOffset("x"), n.calculateAnimOffset("y"), Ae(), n.calculateOverBoundsAnimOffset("x"), n.calculateOverBoundsAnimOffset("y"), n.speedDecelerationRatioAbs.x < .05 && n.speedDecelerationRatioAbs.y < .05)) return ue.x = Math.round(ue.x), ue.y = Math.round(ue.y), Ae(), void Ue("zoomPan")
                                }
                            };
                            return n
                        }, Dt = function (e) {
                            if (e.calculateSwipeSpeed("y"), J = r.currItem.bounds, e.backAnimDestination = {}, e.backAnimStarted = {}, Math.abs(e.lastFlickSpeed.x) <= .05 && Math.abs(e.lastFlickSpeed.y) <= .05) return e.speedDecelerationRatioAbs.x = e.speedDecelerationRatioAbs.y = 0, e.calculateOverBoundsAnimOffset("x"), e.calculateOverBoundsAnimOffset("y"), !0;
                            Ge("zoomPan"), e.lastNow = Ee(), e.panAnimLoop()
                        }, Lt = function (e, t) {
                            var n, i, a;
                            if (te || (st = u), "swipe" === e) {
                                var l = et.x - tt.x, c = t.lastFlickDist.x < 10;
                                l > 30 && (c || t.lastFlickOffset.x > 20) ? i = -1 : l < -30 && (c || t.lastFlickOffset.x < -20) && (i = 1)
                            }
                            i && ((u += i) < 0 ? (u = s.loop ? Rt() - 1 : 0, a = !0) : u >= Rt() && (u = s.loop ? 0 : Rt() - 1, a = !0), a && !s.loop || (me += i, pe -= i, n = !0));
                            var d, p = he.x * pe, f = Math.abs(p - ct.x);
                            return n || p > ct.x == t.lastFlickSpeed.x > 0 ? (d = Math.abs(t.lastFlickSpeed.x) > 0 ? f / Math.abs(t.lastFlickSpeed.x) : 333, d = Math.min(d, 400), d = Math.max(d, 250)) : d = 333, st === u && (n = !1), te = !0, Ce("mainScrollAnimStart"), Ke("mainScroll", ct.x, p, d, o.easing.cubic.out, De, function () {
                                Ye(), te = !1, st = -1, (n || st !== u) && r.updateCurrItem(), Ce("mainScrollAnimComplete")
                            }), n && r.updateCurrItem(!0), n
                        }, Nt = function (e) {
                            return 1 / X * e * v
                        }, Pt = function () {
                            var e = g, t = $e(), n = Fe();
                            g < t ? e = t : g > n && (e = n);
                            var i, s = re;
                            return oe && !W && !se && g < t ? (r.close(), !0) : (oe && (i = function (e) {
                                ke((1 - s) * e + s)
                            }), r.zoomTo(e, 0, 200, o.easing.cubic.out, i), !0)
                        };
                    we("Gestures", {
                        publicMethods: {
                            initGestures: function () {
                                var e = function (e, t, n, i, o) {
                                    C = e + t, E = e + n, k = e + i, _ = o ? e + o : ""
                                };
                                (I = H.pointerEvent) && H.touch && (H.touch = !1), I ? navigator.pointerEnabled ? e("pointer", "down", "move", "up", "cancel") : e("MSPointer", "Down", "Move", "Up", "Cancel") : H.touch ? (e("touch", "start", "move", "end", "cancel"), O = !0) : e("mouse", "down", "move", "up"), f = E + " " + k + " " + _, h = C, I && !O && (O = navigator.maxTouchPoints > 1 || navigator.msMaxTouchPoints > 1), r.likelyTouchDevice = O, m[C] = kt, m[E] = _t, m[k] = It, _ && (m[_] = m[k]), H.touch && (h += " mousedown", f += " mousemove mouseup", m.mousedown = m[C], m.mousemove = m[E], m.mouseup = m[k]), O || (s.allowPanToNext = !1)
                            }
                        }
                    });
                    var Mt, jt, Ht, $t, Ft, Rt, Wt = function (t, n, i, a) {
                        var l;
                        Mt && clearTimeout(Mt), $t = !0, Ht = !0, t.initialLayout ? (l = t.initialLayout, t.initialLayout = null) : l = s.getThumbBoundsFn && s.getThumbBoundsFn(u);
                        var d = i ? s.hideAnimationDuration : s.showAnimationDuration, p = function () {
                            Ue("initialZoom"), i ? (r.template.removeAttribute("style"), r.bg.removeAttribute("style")) : (ke(1), n && (n.style.display = "block"), o.addClass(e, "pswp--animated-in"), Ce("initialZoom" + (i ? "OutEnd" : "InEnd"))), a && a(), $t = !1
                        };
                        if (!d || !l || void 0 === l.x) return Ce("initialZoom" + (i ? "Out" : "In")), g = t.initialZoomLevel, Ne(ue, t.initialPosition), Ae(), e.style.opacity = i ? 0 : 1, ke(1), void (d ? setTimeout(function () {
                            p()
                        }, d) : p());
                        !function () {
                            var n = c, a = !r.currItem.src || r.currItem.loadError || s.showHideOpacity;
                            t.miniImg && (t.miniImg.style.webkitBackfaceVisibility = "hidden"), i || (g = l.w / t.w, ue.x = l.x, ue.y = l.y - P, r[a ? "template" : "bg"].style.opacity = .001, Ae()), Ge("initialZoom"), i && !n && o.removeClass(e, "pswp--animated-in"), a && (i ? o[(n ? "remove" : "add") + "Class"](e, "pswp--animate_opacity") : setTimeout(function () {
                                o.addClass(e, "pswp--animate_opacity")
                            }, 30)), Mt = setTimeout(function () {
                                if (Ce("initialZoom" + (i ? "Out" : "In")), i) {
                                    var r = l.w / t.w, s = {x: ue.x, y: ue.y}, c = g, u = re, f = function (t) {
                                        1 === t ? (g = r, ue.x = l.x, ue.y = l.y - j) : (g = (r - c) * t + c, ue.x = (l.x - s.x) * t + s.x, ue.y = (l.y - j - s.y) * t + s.y), Ae(), a ? e.style.opacity = 1 - t : ke(u - t * u)
                                    };
                                    n ? Ke("initialZoom", 0, 1, d, o.easing.cubic.out, f, p) : (f(1), Mt = setTimeout(p, d + 20))
                                } else g = t.initialZoomLevel, Ne(ue, t.initialPosition), Ae(), ke(1), a ? e.style.opacity = 1 : ke(1), Mt = setTimeout(p, d + 20)
                            }, i ? 25 : 90)
                        }()
                    }, Vt = {}, zt = [], qt = {
                        index: 0,
                        errorMsg: '<div class="pswp__error-msg"><a href="%url%" target="_blank">The image</a> could not be loaded.</div>',
                        forceProgressiveLoading: !1,
                        preload: [1, 1],
                        getNumItemsFn: function () {
                            return jt.length
                        }
                    }, Bt = function (e, t, n) {
                        if (e.src && !e.loadError) {
                            var i = !n;
                            if (i && (e.vGap || (e.vGap = {
                                top: 0,
                                bottom: 0
                            }), Ce("parseVerticalMargin", e)), Vt.x = t.x, Vt.y = t.y - e.vGap.top - e.vGap.bottom, i) {
                                var o = Vt.x / e.w, r = Vt.y / e.h;
                                e.fitRatio = o < r ? o : r;
                                var a = s.scaleMode;
                                "orig" === a ? n = 1 : "fit" === a && (n = e.fitRatio), n > 1 && (n = 1), e.initialZoomLevel = n, e.bounds || (e.bounds = {
                                    center: {
                                        x: 0,
                                        y: 0
                                    }, max: {x: 0, y: 0}, min: {x: 0, y: 0}
                                })
                            }
                            if (!n) return;
                            return function (e, t, n) {
                                var i = e.bounds;
                                i.center.x = Math.round((Vt.x - t) / 2), i.center.y = Math.round((Vt.y - n) / 2) + e.vGap.top, i.max.x = t > Vt.x ? Math.round(Vt.x - t) : i.center.x, i.max.y = n > Vt.y ? Math.round(Vt.y - n) + e.vGap.top : i.center.y, i.min.x = t > Vt.x ? 0 : i.center.x, i.min.y = n > Vt.y ? e.vGap.top : i.center.y
                            }(e, e.w * n, e.h * n), i && n === e.initialZoomLevel && (e.initialPosition = e.bounds.center), e.bounds
                        }
                        return e.w = e.h = 0, e.initialZoomLevel = e.fitRatio = 1, e.bounds = {
                            center: {x: 0, y: 0},
                            max: {x: 0, y: 0},
                            min: {x: 0, y: 0}
                        }, e.initialPosition = e.bounds.center, e.bounds
                    }, Ut = function (e, t, n, i, o, s) {
                        t.loadError || i && (t.imageAppended = !0, Kt(t, i, t === r.currItem && be), n.appendChild(i), s && setTimeout(function () {
                            t && t.loaded && t.placeholder && (t.placeholder.style.display = "none", t.placeholder = null)
                        }, 500))
                    }, Gt = function (e) {
                        e.loading = !0, e.loaded = !1;
                        var t = e.img = o.createEl("pswp__img", "img"), n = function () {
                            e.loading = !1, e.loaded = !0, e.loadComplete ? e.loadComplete(e) : e.img = null, t.onload = t.onerror = null, t = null
                        };
                        return t.onload = n, t.onerror = function () {
                            e.loadError = !0, n()
                        }, t.src = e.src, t
                    }, Yt = function (e, t) {
                        if (e.src && e.loadError && e.container) return t && (e.container.innerHTML = ""), e.container.innerHTML = s.errorMsg.replace("%url%", e.src), !0
                    }, Kt = function (e, t, n) {
                        if (e.src) {
                            t || (t = e.container.lastChild);
                            var i = n ? e.w : Math.round(e.w * e.fitRatio), o = n ? e.h : Math.round(e.h * e.fitRatio);
                            e.placeholder && !e.loaded && (e.placeholder.style.width = i + "px", e.placeholder.style.height = o + "px"), t.style.width = i + "px", t.style.height = o + "px"
                        }
                    }, Qt = function () {
                        if (zt.length) {
                            for (var e, t = 0; t < zt.length; t++) (e = zt[t]).holder.index === e.index && Ut(e.index, e.item, e.baseDiv, e.img, 0, e.clearPlaceholder);
                            zt = []
                        }
                    };
                    we("Controller", {
                        publicMethods: {
                            lazyLoadItem: function (e) {
                                e = xe(e);
                                var t = Ft(e);
                                t && (!t.loaded && !t.loading || x) && (Ce("gettingData", e, t), t.src && Gt(t))
                            }, initController: function () {
                                o.extend(s, qt, !0), r.items = jt = n, Ft = r.getItemAt, Rt = s.getNumItemsFn, s.loop, Rt() < 3 && (s.loop = !1), Se("beforeChange", function (e) {
                                    var t, n = s.preload, i = null === e || e >= 0, o = Math.min(n[0], Rt()),
                                        a = Math.min(n[1], Rt());
                                    for (t = 1; t <= (i ? a : o); t++) r.lazyLoadItem(u + t);
                                    for (t = 1; t <= (i ? o : a); t++) r.lazyLoadItem(u - t)
                                }), Se("initialLayout", function () {
                                    r.currItem.initialLayout = s.getThumbBoundsFn && s.getThumbBoundsFn(u)
                                }), Se("mainScrollAnimComplete", Qt), Se("initialZoomInEnd", Qt), Se("destroy", function () {
                                    for (var e, t = 0; t < jt.length; t++) (e = jt[t]).container && (e.container = null), e.placeholder && (e.placeholder = null), e.img && (e.img = null), e.preloader && (e.preloader = null), e.loadError && (e.loaded = e.loadError = !1);
                                    zt = null
                                })
                            }, getItemAt: function (e) {
                                return e >= 0 && void 0 !== jt[e] && jt[e]
                            }, allowProgressiveImg: function () {
                                return s.forceProgressiveLoading || !O || s.mouseUsed || screen.width > 1200
                            }, setContent: function (e, t) {
                                s.loop && (t = xe(t));
                                var n = r.getItemAt(e.index);
                                n && (n.container = null);
                                var i, l = r.getItemAt(t);
                                if (l) {
                                    Ce("gettingData", t, l), e.index = t, e.item = l;
                                    var c = l.container = o.createEl("pswp__zoom-wrap");
                                    if (!l.src && l.html && (l.html.tagName ? c.appendChild(l.html) : c.innerHTML = l.html), Yt(l), Bt(l, de), !l.src || l.loadError || l.loaded) l.src && !l.loadError && ((i = o.createEl("pswp__img", "img")).style.opacity = 1, i.src = l.src, Kt(l, i), Ut(0, l, c, i)); else {
                                        if (l.loadComplete = function (n) {
                                            if (a) {
                                                if (e && e.index === t) {
                                                    if (Yt(n, !0)) return n.loadComplete = n.img = null, Bt(n, de), Ie(n), void (e.index === u && r.updateCurrZoomItem());
                                                    n.imageAppended ? !$t && n.placeholder && (n.placeholder.style.display = "none", n.placeholder = null) : H.transform && (te || $t) ? zt.push({
                                                        item: n,
                                                        baseDiv: c,
                                                        img: n.img,
                                                        index: t,
                                                        holder: e,
                                                        clearPlaceholder: !0
                                                    }) : Ut(0, n, c, n.img, 0, !0)
                                                }
                                                n.loadComplete = null, n.img = null, Ce("imageLoadComplete", t, n)
                                            }
                                        }, o.features.transform) {
                                            var d = "pswp__img pswp__img--placeholder";
                                            d += l.msrc ? "" : " pswp__img--placeholder--blank";
                                            var p = o.createEl(d, l.msrc ? "img" : "");
                                            l.msrc && (p.src = l.msrc), Kt(l, p), c.appendChild(p), l.placeholder = p
                                        }
                                        l.loading || Gt(l), r.allowProgressiveImg() && (!Ht && H.transform ? zt.push({
                                            item: l,
                                            baseDiv: c,
                                            img: l.img,
                                            index: t,
                                            holder: e
                                        }) : Ut(0, l, c, l.img, 0, !0))
                                    }
                                    Ht || t !== u ? Ie(l) : (ee = c.style, Wt(l, i || l.img)), e.el.innerHTML = "", e.el.appendChild(c)
                                } else e.el.innerHTML = ""
                            }, cleanSlide: function (e) {
                                e.img && (e.img.onload = e.img.onerror = null), e.loaded = e.loading = e.img = e.imageAppended = !1
                            }
                        }
                    });
                    var Zt, Xt, Jt = {}, en = function (e, t, n) {
                        var i = document.createEvent("CustomEvent"),
                            o = {origEvent: e, target: e.target, releasePoint: t, pointerType: n || "touch"};
                        i.initCustomEvent("pswpTap", !0, !0, o), e.target.dispatchEvent(i)
                    };
                    we("Tap", {
                        publicMethods: {
                            initTap: function () {
                                Se("firstTouchStart", r.onTapStart), Se("touchRelease", r.onTapRelease), Se("destroy", function () {
                                    Jt = {}, Zt = null
                                })
                            }, onTapStart: function (e) {
                                e.length > 1 && (clearTimeout(Zt), Zt = null)
                            }, onTapRelease: function (e, t) {
                                if (t && !G && !B && !Be) {
                                    var n = t;
                                    if (Zt && (clearTimeout(Zt), Zt = null, function (e, t) {
                                        return Math.abs(e.x - t.x) < 25 && Math.abs(e.y - t.y) < 25
                                    }(n, Jt))) return void Ce("doubleTap", n);
                                    if ("mouse" === t.type) return void en(e, t, "mouse");
                                    var i = e.target.tagName.toUpperCase();
                                    if ("BUTTON" === i || o.hasClass(e.target, "pswp__single-tap")) return void en(e, t);
                                    Ne(Jt, n), Zt = setTimeout(function () {
                                        en(e, t), Zt = null
                                    }, 300)
                                }
                            }
                        }
                    }), we("DesktopZoom", {
                        publicMethods: {
                            initDesktopZoom: function () {
                                M || (O ? Se("mouseUsed", function () {
                                    r.setupDesktopZoom()
                                }) : r.setupDesktopZoom(!0))
                            }, setupDesktopZoom: function (t) {
                                Xt = {};
                                var n = "wheel mousewheel DOMMouseScroll";
                                Se("bindEvents", function () {
                                    o.bind(e, n, r.handleMouseWheel)
                                }), Se("unbindEvents", function () {
                                    Xt && o.unbind(e, n, r.handleMouseWheel)
                                }), r.mouseZoomedIn = !1;
                                var i, s = function () {
                                    r.mouseZoomedIn && (o.removeClass(e, "pswp--zoomed-in"), r.mouseZoomedIn = !1), g < 1 ? o.addClass(e, "pswp--zoom-allowed") : o.removeClass(e, "pswp--zoom-allowed"), a()
                                }, a = function () {
                                    i && (o.removeClass(e, "pswp--dragging"), i = !1)
                                };
                                Se("resize", s), Se("afterChange", s), Se("pointerDown", function () {
                                    r.mouseZoomedIn && (i = !0, o.addClass(e, "pswp--dragging"))
                                }), Se("pointerUp", a), t || s()
                            }, handleMouseWheel: function (e) {
                                if (g <= r.currItem.fitRatio) return s.modal && (!s.closeOnScroll || Be || q ? e.preventDefault() : A && Math.abs(e.deltaY) > 2 && (c = !0, r.close())), !0;
                                if (e.stopPropagation(), Xt.x = 0, "deltaX" in e) 1 === e.deltaMode ? (Xt.x = 18 * e.deltaX, Xt.y = 18 * e.deltaY) : (Xt.x = e.deltaX, Xt.y = e.deltaY); else if ("wheelDelta" in e) e.wheelDeltaX && (Xt.x = -.16 * e.wheelDeltaX), e.wheelDeltaY ? Xt.y = -.16 * e.wheelDeltaY : Xt.y = -.16 * e.wheelDelta; else {
                                    if (!("detail" in e)) return;
                                    Xt.y = e.detail
                                }
                                He(g, !0);
                                var t = ue.x - Xt.x, n = ue.y - Xt.y;
                                (s.modal || t <= J.min.x && t >= J.max.x && n <= J.min.y && n >= J.max.y) && e.preventDefault(), r.panTo(t, n)
                            }, toggleDesktopZoom: function (t) {
                                t = t || {x: de.x / 2 + fe.x, y: de.y / 2 + fe.y};
                                var n = s.getDoubleTapZoom(!0, r.currItem), i = g === n;
                                r.mouseZoomedIn = !i, r.zoomTo(i ? r.currItem.initialZoomLevel : n, t, 333), o[(i ? "remove" : "add") + "Class"](e, "pswp--zoomed-in")
                            }
                        }
                    });
                    var tn, nn, on, rn, sn, an, ln, cn, un, dn, pn, fn, hn = {history: !0, galleryUID: 1},
                        mn = function () {
                            return pn.hash.substring(1)
                        }, gn = function () {
                            tn && clearTimeout(tn), on && clearTimeout(on)
                        }, vn = function () {
                            var e = mn(), t = {};
                            if (e.length < 5) return t;
                            var n, i = e.split("&");
                            for (n = 0; n < i.length; n++) if (i[n]) {
                                var o = i[n].split("=");
                                o.length < 2 || (t[o[0]] = o[1])
                            }
                            if (s.galleryPIDs) {
                                var r = t.pid;
                                for (t.pid = 0, n = 0; n < jt.length; n++) if (jt[n].pid === r) {
                                    t.pid = n;
                                    break
                                }
                            } else t.pid = parseInt(t.pid, 10) - 1;
                            return t.pid < 0 && (t.pid = 0), t
                        }, yn = function () {
                            if (on && clearTimeout(on), Be || q) on = setTimeout(yn, 500); else {
                                rn ? clearTimeout(nn) : rn = !0;
                                var e = u + 1, t = Ft(u);
                                t.hasOwnProperty("pid") && (e = t.pid);
                                var n = ln + "&gid=" + s.galleryUID + "&pid=" + e;
                                cn || -1 === pn.hash.indexOf(n) && (dn = !0);
                                var i = pn.href.split("#")[0] + "#" + n;
                                fn ? "#" + n !== window.location.hash && history[cn ? "replaceState" : "pushState"]("", document.title, i) : cn ? pn.replace(i) : pn.hash = n, cn = !0, nn = setTimeout(function () {
                                    rn = !1
                                }, 60)
                            }
                        };
                    we("History", {
                        publicMethods: {
                            initHistory: function () {
                                if (o.extend(s, hn, !0), s.history) {
                                    pn = window.location, dn = !1, un = !1, cn = !1, ln = mn(), fn = "pushState" in history, ln.indexOf("gid=") > -1 && (ln = (ln = ln.split("&gid=")[0]).split("?gid=")[0]), Se("afterChange", r.updateURL), Se("unbindEvents", function () {
                                        o.unbind(window, "hashchange", r.onHashChange)
                                    });
                                    var e = function () {
                                        an = !0, un || (dn ? history.back() : ln ? pn.hash = ln : fn ? history.pushState("", document.title, pn.pathname + pn.search) : pn.hash = ""), gn()
                                    };
                                    Se("unbindEvents", function () {
                                        c && e()
                                    }), Se("destroy", function () {
                                        an || e()
                                    }), Se("firstUpdate", function () {
                                        u = vn().pid
                                    });
                                    var t = ln.indexOf("pid=");
                                    t > -1 && "&" === (ln = ln.substring(0, t)).slice(-1) && (ln = ln.slice(0, -1)), setTimeout(function () {
                                        a && o.bind(window, "hashchange", r.onHashChange)
                                    }, 40)
                                }
                            }, onHashChange: function () {
                                if (mn() === ln) return un = !0, void r.close();
                                rn || (sn = !0, r.goTo(vn().pid), sn = !1)
                            }, updateURL: function () {
                                gn(), sn || (cn ? tn = setTimeout(yn, 800) : yn())
                            }
                        }
                    }), o.extend(r, Qe)
                }
            }) ? o.call(i, n, i, t) : o) || (t.exports = r)
        }, {}], 2: [function (e, t, n) {
            "use strict";
            Object.defineProperty(n, "__esModule", {value: !0}), n.PhotoSwipe = n.default = void 0;
            var i = r(e("photoswipe")), o = r(e("./libs/photoswipe-ui-default"));

            function r(e) {
                return e && e.__esModule ? e : {default: e}
            }

            function s(e) {
                var t = e('<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true"><div class="pswp__bg"></div><div class="pswp__scroll-wrap"><div class="pswp__container"><div class="pswp__item"></div><div class="pswp__item"></div><div class="pswp__item"></div></div><div class="pswp__ui pswp__ui--hidden"><div class="pswp__top-bar"><div class="pswp__counter"></div><button class="pswp__button pswp__button--close" title="Close (Esc)"></button> <button class="pswp__button pswp__button--share" title="Share"></button> <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button> <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button><div class="pswp__preloader"><div class="pswp__preloader__icn"><div class="pswp__preloader__cut"><div class="pswp__preloader__donut"></div></div></div></div></div><div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap"><div class="pswp__share-tooltip"></div></div><button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button> <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button><div class="pswp__caption"><div class="pswp__caption__center"></div></div></div></div></div>').appendTo("body"),
                    n = 1;

                function r(t) {
                    var n = u(t).slideSelector;
                    return t.find(n).map(function (t) {
                        var n = e(this).data("index", t), i = this.tagName.toUpperCase();
                        return "A" === i ? this.hash ? n = e(this.hash) : (n = n.find("img").eq(0)).data("original-src", this.href) : "IMG" !== i && (n = n.find("img")), n[0]
                    })
                }

                function s(t, n) {
                    var i = e.Deferred(), o = n.data("original-src-" + t),
                        r = decodeURI(n.data("original-src") || n.attr("src")).match(/(\d+)[*×x](\d+)/);
                    return o ? i.resolve(o) : null !== r ? i.resolve(r["width" === t ? 1 : 2]) : e("<img>").on("load", function () {
                        i.resolve(this[t])
                    }).attr("src", n.attr("src")), i.promise()
                }

                function a(t) {
                    return e.when(function (e) {
                        return s("width", e)
                    }(t), function (e) {
                        return s("height", e)
                    }(t))
                }

                function l() {
                    var t = e(this), n = t.data("original-src") || t.attr("src"), i = e.Deferred();
                    return "IMG" !== this.tagName ? i.resolve({html: this.innerHTML}) : a(t).done(function (e, o) {
                        var r, s, a, l = t.attr("src");
                        r = (s = t.data("caption-class")) ? function e(t, n) {
                            var i, o = t.parent();
                            if (o.length) return (i = o.find(n)).length ? i.html() : e(o, n)
                        }(t, "." + s) : (a = t.closest("figure").find("figcaption")) && a.length ? a.html() : t.attr("alt"), i.resolve({
                            w: e,
                            h: o,
                            src: n,
                            msrc: l,
                            title: r
                        })
                    }), i.promise()
                }

                function c(t) {
                    var n = t.map(l).get(), i = e.Deferred();
                    return e.when.apply(e, n).done(function () {
                        var e = Array.prototype.slice.call(arguments);
                        i.resolve(e)
                    }), i.promise()
                }

                function u(e) {
                    return e.data("photoswipeOptions")
                }

                function d(n, r, s, a) {
                    var l = e.extend(u(r).globalOptions, {
                        index: n, getThumbBoundsFn: function (e) {
                            return function (t) {
                                var n = e.eq(t), i = n.offset(), o = n[0].width;
                                return {x: i.left, y: i.top, w: o}
                            }
                        }(s), galleryUID: r.data("pswp-uid")
                    }), c = new i.default(t[0], o.default, a, l);
                    e.each(u(r).events, function (e, t) {
                        c.listen(e, t)
                    }), c.init()
                }

                function p(e) {
                    var t = function () {
                        var e = window.location.hash.substring(1), t = {};
                        if (e.length < 5) return t;
                        for (var n = e.split("&"), i = 0; i < n.length; i++) if (n[i]) {
                            var o = n[i].split("=");
                            o.length < 2 || (t[o[0]] = parseInt(o[1], 10))
                        }
                        return t
                    }();
                    t.pid && t.gid && function () {
                        var n = e[t.gid - 1], i = t.pid - 1, o = r(n);
                        c(o).done(function (e) {
                            d(i, n, o, e)
                        })
                    }()
                }

                function f(t, n, i) {
                    t.on("click.photoswipe", u(t).slideSelector, function (o) {
                        o.preventDefault(), d(e(this).data("index"), t, n, i)
                    })
                }

                e.fn.photoSwipe = function () {
                    var t = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : "img",
                        i = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {},
                        o = arguments.length > 2 && void 0 !== arguments[2] ? arguments[2] : {},
                        s = e.extend({bgOpacity: .973, showHideOpacity: !0}, i), a = [], l = "update" === t;
                    return this.each(function () {
                        if (l) !function (e) {
                            var t = r(e);
                            c(t).done(function (n) {
                                !function (e) {
                                    e.off("click.photoswipe")
                                }(e), f(e, t, n)
                            })
                        }(e(this)); else {
                            var i = e(this).data("photoswipeOptions", {slideSelector: t, globalOptions: s, events: o}),
                                u = r(i), d = c(u);
                            !function (e) {
                                e.data("pswp-uid") || e.data("pswp-uid", n++)
                            }(i), a.push(i), d.done(function (e) {
                                f(i, u, e)
                            })
                        }
                    }), l || p(a), this
                }, e.fn.photoSwipe.PhotoSwipe = i.default
            }

            s(jQuery), n.default = s, n.PhotoSwipe = i.default
        }, {"./libs/photoswipe-ui-default": 3, photoswipe: 1}], 3: [function (e, t, i) {
            "use strict";
            "function" == typeof Symbol && Symbol.iterator;
            /*! PhotoSwipe Default UI - 4.1.1 - 2015-12-24
* http://photoswipe.com
* Copyright (c) 2015 Dmitry Semenov; */
            void 0 === (r = "function" == typeof (o = function () {
                return function (e, t) {
                    var n, i, o, r, s, a, l, c, u, d, p, f, h, m, g, v, y, b, w = this, x = !1, T = !0, S = !0, C = {
                        barsSize: {top: 44, bottom: "auto"},
                        closeElClasses: ["item", "caption", "zoom-wrap", "ui", "top-bar"],
                        timeToIdle: 4e3,
                        timeToIdleOutside: 1e3,
                        loadingIndicatorDelay: 1e3,
                        addCaptionHTMLFn: function (e, t) {
                            return e.title ? (t.children[0].innerHTML = e.title, !0) : (t.children[0].innerHTML = "", !1)
                        },
                        closeEl: !0,
                        captionEl: !0,
                        fullscreenEl: !0,
                        zoomEl: !0,
                        shareEl: !0,
                        counterEl: !0,
                        arrowEl: !0,
                        preloaderEl: !0,
                        tapToClose: !1,
                        tapToToggleControls: !0,
                        clickToCloseNonZoomable: !0,
                        shareButtons: [{
                            id: "facebook",
                            label: "Share on Facebook",
                            url: "https://www.facebook.com/sharer/sharer.php?u={{url}}"
                        }, {
                            id: "twitter",
                            label: "Tweet",
                            url: "https://twitter.com/intent/tweet?text={{text}}&url={{url}}"
                        }, {
                            id: "pinterest",
                            label: "Pin it",
                            url: "http://www.pinterest.com/pin/create/button/?url={{url}}&media={{image_url}}&description={{text}}"
                        }, {id: "download", label: "Download image", url: "{{raw_image_url}}", download: !0}],
                        getImageURLForShare: function () {
                            return e.currItem.src || ""
                        },
                        getPageURLForShare: function () {
                            return window.location.href
                        },
                        getTextForShare: function () {
                            return e.currItem.title || ""
                        },
                        indexIndicatorSep: " / ",
                        fitControlsWidth: 1200
                    }, E = function (e) {
                        if (v) return !0;
                        e = e || window.event, g.timeToIdle && g.mouseUsed && !u && P();
                        for (var n, i, o = e.target || e.srcElement, r = o.getAttribute("class") || "", s = 0; s < $.length; s++) (n = $[s]).onTap && r.indexOf("pswp__" + n.name) > -1 && (n.onTap(), i = !0);
                        if (i) {
                            e.stopPropagation && e.stopPropagation(), v = !0;
                            var a = t.features.isOldAndroid ? 600 : 30;
                            setTimeout(function () {
                                v = !1
                            }, a)
                        }
                    }, k = function (e, n, i) {
                        t[(i ? "add" : "remove") + "Class"](e, "pswp__" + n)
                    }, _ = function () {
                        var e = 1 === g.getNumItemsFn();
                        e !== m && (k(i, "ui--one-slide", e), m = e)
                    }, A = function () {
                        k(l, "share-modal--hidden", S)
                    }, I = function () {
                        return (S = !S) ? (t.removeClass(l, "pswp__share-modal--fade-in"), setTimeout(function () {
                            S && A()
                        }, 300)) : (A(), setTimeout(function () {
                            S || t.addClass(l, "pswp__share-modal--fade-in")
                        }, 30)), S || D(), !1
                    }, O = function (t) {
                        var n = (t = t || window.event).target || t.srcElement;
                        return e.shout("shareLinkClick", t, n), !(!n.href || !n.hasAttribute("download") && (window.open(n.href, "pswp_share", "scrollbars=yes,resizable=yes,toolbar=no,location=yes,width=550,height=420,top=100,left=" + (window.screen ? Math.round(screen.width / 2 - 275) : 100)), S || I(), 1))
                    }, D = function () {
                        for (var e, t, n, i, o, r = "", s = 0; s < g.shareButtons.length; s++) e = g.shareButtons[s], n = g.getImageURLForShare(e), i = g.getPageURLForShare(e), o = g.getTextForShare(e), t = e.url.replace("{{url}}", encodeURIComponent(i)).replace("{{image_url}}", encodeURIComponent(n)).replace("{{raw_image_url}}", n).replace("{{text}}", encodeURIComponent(o)), r += '<a href="' + t + '" target="_blank" class="pswp__share--' + e.id + '"' + (e.download ? "download" : "") + ">" + e.label + "</a>", g.parseShareButtonOut && (r = g.parseShareButtonOut(e, r));
                        l.children[0].innerHTML = r, l.children[0].onclick = O
                    }, L = function (e) {
                        for (var n = 0; n < g.closeElClasses.length; n++) if (t.hasClass(e, "pswp__" + g.closeElClasses[n])) return !0
                    }, N = 0, P = function () {
                        clearTimeout(b), N = 0, u && w.setIdle(!1)
                    }, M = function (e) {
                        var t = (e = e || window.event).relatedTarget || e.toElement;
                        t && "HTML" !== t.nodeName || (clearTimeout(b), b = setTimeout(function () {
                            w.setIdle(!0)
                        }, g.timeToIdleOutside))
                    }, j = function (e) {
                        f !== e && (k(p, "preloader--active", !e), f = e)
                    }, H = function (n) {
                        var s = n.vGap;
                        if (!e.likelyTouchDevice || g.mouseUsed || screen.width > g.fitControlsWidth) {
                            var a = g.barsSize;
                            if (g.captionEl && "auto" === a.bottom) if (r || ((r = t.createEl("pswp__caption pswp__caption--fake")).appendChild(t.createEl("pswp__caption__center")), i.insertBefore(r, o), t.addClass(i, "pswp__ui--fit")), g.addCaptionHTMLFn(n, r, !0)) {
                                var l = r.clientHeight;
                                s.bottom = parseInt(l, 10) || 44
                            } else s.bottom = a.top; else s.bottom = "auto" === a.bottom ? 0 : a.bottom;
                            s.top = a.top
                        } else s.top = s.bottom = 0
                    }, $ = [{
                        name: "caption", option: "captionEl", onInit: function (e) {
                            o = e
                        }
                    }, {
                        name: "share-modal", option: "shareEl", onInit: function (e) {
                            l = e
                        }, onTap: function () {
                            I()
                        }
                    }, {
                        name: "button--share", option: "shareEl", onInit: function (e) {
                            a = e
                        }, onTap: function () {
                            I()
                        }
                    }, {name: "button--zoom", option: "zoomEl", onTap: e.toggleDesktopZoom}, {
                        name: "counter",
                        option: "counterEl",
                        onInit: function (e) {
                            s = e
                        }
                    }, {name: "button--close", option: "closeEl", onTap: e.close}, {
                        name: "button--arrow--left",
                        option: "arrowEl",
                        onTap: e.prev
                    }, {name: "button--arrow--right", option: "arrowEl", onTap: e.next}, {
                        name: "button--fs",
                        option: "fullscreenEl",
                        onTap: function () {
                            n.isFullscreen() ? n.exit() : n.enter()
                        }
                    }, {
                        name: "preloader", option: "preloaderEl", onInit: function (e) {
                            p = e
                        }
                    }];
                    w.init = function () {
                        t.extend(e.options, C, !0), g = e.options, i = t.getChildByClass(e.scrollWrap, "pswp__ui"), d = e.listen, function () {
                            var e;
                            d("onVerticalDrag", function (e) {
                                T && e < .95 ? w.hideControls() : !T && e >= .95 && w.showControls()
                            }), d("onPinchClose", function (t) {
                                T && t < .9 ? (w.hideControls(), e = !0) : e && !T && t > .9 && w.showControls()
                            }), d("zoomGestureEnded", function () {
                                (e = !1) && !T && w.showControls()
                            })
                        }(), d("beforeChange", w.update), d("doubleTap", function (t) {
                            var n = e.currItem.initialZoomLevel;
                            e.getZoomLevel() !== n ? e.zoomTo(n, t, 333) : e.zoomTo(g.getDoubleTapZoom(!1, e.currItem), t, 333)
                        }), d("preventDragEvent", function (e, t, n) {
                            var i = e.target || e.srcElement;
                            i && i.getAttribute("class") && e.type.indexOf("mouse") > -1 && (i.getAttribute("class").indexOf("__caption") > 0 || /(SMALL|STRONG|EM)/i.test(i.tagName)) && (n.prevent = !1)
                        }), d("bindEvents", function () {
                            t.bind(i, "pswpTap click", E), t.bind(e.scrollWrap, "pswpTap", w.onGlobalTap), e.likelyTouchDevice || t.bind(e.scrollWrap, "mouseover", w.onMouseOver)
                        }), d("unbindEvents", function () {
                            S || I(), y && clearInterval(y), t.unbind(document, "mouseout", M), t.unbind(document, "mousemove", P), t.unbind(i, "pswpTap click", E), t.unbind(e.scrollWrap, "pswpTap", w.onGlobalTap), t.unbind(e.scrollWrap, "mouseover", w.onMouseOver), n && (t.unbind(document, n.eventK, w.updateFullscreen), n.isFullscreen() && (g.hideAnimationDuration = 0, n.exit()), n = null)
                        }), d("destroy", function () {
                            g.captionEl && (r && i.removeChild(r), t.removeClass(o, "pswp__caption--empty")), l && (l.children[0].onclick = null), t.removeClass(i, "pswp__ui--over-close"), t.addClass(i, "pswp__ui--hidden"), w.setIdle(!1)
                        }), g.showAnimationDuration || t.removeClass(i, "pswp__ui--hidden"), d("initialZoomIn", function () {
                            g.showAnimationDuration && t.removeClass(i, "pswp__ui--hidden")
                        }), d("initialZoomOut", function () {
                            t.addClass(i, "pswp__ui--hidden")
                        }), d("parseVerticalMargin", H), function () {
                            var e, n, o, r = function (i) {
                                if (i) for (var r = i.length, s = 0; s < r; s++) {
                                    e = i[s], n = e.className;
                                    for (var a = 0; a < $.length; a++) o = $[a], n.indexOf("pswp__" + o.name) > -1 && (g[o.option] ? (t.removeClass(e, "pswp__element--disabled"), o.onInit && o.onInit(e)) : t.addClass(e, "pswp__element--disabled"))
                                }
                            };
                            r(i.children);
                            var s = t.getChildByClass(i, "pswp__top-bar");
                            s && r(s.children)
                        }(), g.shareEl && a && l && (S = !0), _(), g.timeToIdle && d("mouseUsed", function () {
                            t.bind(document, "mousemove", P), t.bind(document, "mouseout", M), y = setInterval(function () {
                                2 == ++N && w.setIdle(!0)
                            }, g.timeToIdle / 2)
                        }), g.fullscreenEl && !t.features.isOldAndroid && (n || (n = w.getFullscreenAPI()), n ? (t.bind(document, n.eventK, w.updateFullscreen), w.updateFullscreen(), t.addClass(e.template, "pswp--supports-fs")) : t.removeClass(e.template, "pswp--supports-fs")), g.preloaderEl && (j(!0), d("beforeChange", function () {
                            clearTimeout(h), h = setTimeout(function () {
                                e.currItem && e.currItem.loading ? (!e.allowProgressiveImg() || e.currItem.img && !e.currItem.img.naturalWidth) && j(!1) : j(!0)
                            }, g.loadingIndicatorDelay)
                        }), d("imageLoadComplete", function (t, n) {
                            e.currItem === n && j(!0)
                        }))
                    }, w.setIdle = function (e) {
                        u = e, k(i, "ui--idle", e)
                    }, w.update = function () {
                        T && e.currItem ? (w.updateIndexIndicator(), g.captionEl && (g.addCaptionHTMLFn(e.currItem, o), k(o, "caption--empty", !e.currItem.title)), x = !0) : x = !1, S || I(), _()
                    }, w.updateFullscreen = function (i) {
                        i && setTimeout(function () {
                            e.setScrollOffset(0, t.getScrollY())
                        }, 50), t[(n.isFullscreen() ? "add" : "remove") + "Class"](e.template, "pswp--fs")
                    }, w.updateIndexIndicator = function () {
                        g.counterEl && (s.innerHTML = e.getCurrentIndex() + 1 + g.indexIndicatorSep + g.getNumItemsFn())
                    }, w.onGlobalTap = function (n) {
                        var i = (n = n || window.event).target || n.srcElement;
                        if (!v) if (n.detail && "mouse" === n.detail.pointerType) {
                            if (L(i)) return void e.close();
                            t.hasClass(i, "pswp__img") && (1 === e.getZoomLevel() && e.getZoomLevel() <= e.currItem.fitRatio ? g.clickToCloseNonZoomable && e.close() : e.toggleDesktopZoom(n.detail.releasePoint))
                        } else if (g.tapToToggleControls && (T ? w.hideControls() : w.showControls()), g.tapToClose && (t.hasClass(i, "pswp__img") || L(i))) return void e.close()
                    }, w.onMouseOver = function (e) {
                        var t = (e = e || window.event).target || e.srcElement;
                        k(i, "ui--over-close", L(t))
                    }, w.hideControls = function () {
                        t.addClass(i, "pswp__ui--hidden"), T = !1
                    }, w.showControls = function () {
                        T = !0, x || w.update(), t.removeClass(i, "pswp__ui--hidden")
                    }, w.supportsFullscreen = function () {
                        var e = document;
                        return !!(e.exitFullscreen || e.mozCancelFullScreen || e.webkitExitFullscreen || e.msExitFullscreen)
                    }, w.getFullscreenAPI = function () {
                        var t, n = document.documentElement, i = "fullscreenchange";
                        return n.requestFullscreen ? t = {
                            enterK: "requestFullscreen",
                            exitK: "exitFullscreen",
                            elementK: "fullscreenElement",
                            eventK: i
                        } : n.mozRequestFullScreen ? t = {
                            enterK: "mozRequestFullScreen",
                            exitK: "mozCancelFullScreen",
                            elementK: "mozFullScreenElement",
                            eventK: "moz" + i
                        } : n.webkitRequestFullscreen ? t = {
                            enterK: "webkitRequestFullscreen",
                            exitK: "webkitExitFullscreen",
                            elementK: "webkitFullscreenElement",
                            eventK: "webkit" + i
                        } : n.msRequestFullscreen && (t = {
                            enterK: "msRequestFullscreen",
                            exitK: "msExitFullscreen",
                            elementK: "msFullscreenElement",
                            eventK: "MSFullscreenChange"
                        }), t && (t.enter = function () {
                            if (c = g.closeOnScroll, g.closeOnScroll = !1, "webkitRequestFullscreen" !== this.enterK) return e.template[this.enterK]();
                            e.template[this.enterK](Element.ALLOW_KEYBOARD_INPUT)
                        }, t.exit = function () {
                            return g.closeOnScroll = c, document[this.exitK]()
                        }, t.isFullscreen = function () {
                            return document[this.elementK]
                        }), t
                    }
                }
            }) ? o.call(i, n, i, t) : o) || (t.exports = r)
        }, {}]
    }, {}, [2])
}, function (e, t, n) {
    window.imagesLoaded = n(57), window.Shuffle = n(58), function (e) {
        page.registerVendor("Shuffle"), page.initShuffle = function () {
            if (void 0 !== window.Shuffle && 0 !== e('[data-provide="shuffle"]').length) {
                var t = window.Shuffle;
                e('[data-provide="shuffle"]').each(function () {
                    var n = e(this).find('[data-shuffle="list"]'), i = e(this).find('[data-shuffle="filter"]'),
                        o = e(this).find('[data-shuffle="search"]'), r = new t(n, {
                            itemSelector: '[data-shuffle="item"]',
                            sizer: '[data-shuffle="sizer"]',
                            delimeter: ",",
                            speed: 500
                        });
                    i.length && e(i).find('[data-shuffle="button"]').each(function () {
                        e(this).on("click", function () {
                            var n, i = e(this), o = i.hasClass("active"), s = i.data("group");
                            e(this).closest('[data-shuffle="filter"]').find('[data-shuffle="button"].active').removeClass("active"), o ? (i.removeClass("active"), n = t.ALL_ITEMS) : (i.addClass("active"), n = s), r.filter(n)
                        })
                    }), o.length && o.on("keyup", function () {
                        var t = e(this).val().toLowerCase();
                        r.filter(function (e, n) {
                            return -1 !== e.textContent.toLowerCase().trim().indexOf(t)
                        })
                    }), e(this).imagesLoaded(function () {
                        r.layout()
                    })
                })
            }
        }
    }(jQuery)
}, function (e, t, n) {
    var i, o, r, s;
    "undefined" != typeof window && window, r = {
        id: "ev-emitter/ev-emitter",
        exports: {},
        loaded: !1
    }, i = "function" == typeof (o = function () {
        function e() {
        }

        var t = e.prototype;
        return t.on = function (e, t) {
            if (e && t) {
                var n = this._events = this._events || {}, i = n[e] = n[e] || [];
                return -1 == i.indexOf(t) && i.push(t), this
            }
        }, t.once = function (e, t) {
            if (e && t) {
                this.on(e, t);
                var n = this._onceEvents = this._onceEvents || {};
                return (n[e] = n[e] || {})[t] = !0, this
            }
        }, t.off = function (e, t) {
            var n = this._events && this._events[e];
            if (n && n.length) {
                var i = n.indexOf(t);
                return -1 != i && n.splice(i, 1), this
            }
        }, t.emitEvent = function (e, t) {
            var n = this._events && this._events[e];
            if (n && n.length) {
                n = n.slice(0), t = t || [];
                for (var i = this._onceEvents && this._onceEvents[e], o = 0; o < n.length; o++) {
                    var r = n[o];
                    i && i[r] && (this.off(e, r), delete i[r]), r.apply(this, t)
                }
                return this
            }
        }, t.allOff = function () {
            delete this._events, delete this._onceEvents
        }, e
    }) ? o.call(r.exports, n, r.exports, r) : o, r.loaded = !0, void 0 !== i || (i = r.exports),
        /*!
 * imagesLoaded v4.1.4
 * JavaScript is all like "You images are done yet or what?"
 * MIT License
 */
        function (n, o) {
            "use strict";
            void 0 === (s = function (e) {
                return function (e, t) {
                    var n = e.jQuery, i = e.console;

                    function o(e, t) {
                        for (var n in t) e[n] = t[n];
                        return e
                    }

                    var r = Array.prototype.slice;

                    function s(e, t, a) {
                        if (!(this instanceof s)) return new s(e, t, a);
                        var l = e;
                        "string" == typeof e && (l = document.querySelectorAll(e)), l ? (this.elements = function (e) {
                            if (Array.isArray(e)) return e;
                            if ("object" == typeof e && "number" == typeof e.length) return r.call(e);
                            return [e]
                        }(l), this.options = o({}, this.options), "function" == typeof t ? a = t : o(this.options, t), a && this.on("always", a), this.getImages(), n && (this.jqDeferred = new n.Deferred), setTimeout(this.check.bind(this))) : i.error("Bad element for imagesLoaded " + (l || e))
                    }

                    s.prototype = Object.create(t.prototype), s.prototype.options = {}, s.prototype.getImages = function () {
                        this.images = [], this.elements.forEach(this.addElementImages, this)
                    }, s.prototype.addElementImages = function (e) {
                        "IMG" == e.nodeName && this.addImage(e), !0 === this.options.background && this.addElementBackgroundImages(e);
                        var t = e.nodeType;
                        if (t && a[t]) {
                            for (var n = e.querySelectorAll("img"), i = 0; i < n.length; i++) {
                                var o = n[i];
                                this.addImage(o)
                            }
                            if ("string" == typeof this.options.background) {
                                var r = e.querySelectorAll(this.options.background);
                                for (i = 0; i < r.length; i++) {
                                    var s = r[i];
                                    this.addElementBackgroundImages(s)
                                }
                            }
                        }
                    };
                    var a = {1: !0, 9: !0, 11: !0};

                    function l(e) {
                        this.img = e
                    }

                    function c(e, t) {
                        this.url = e, this.element = t, this.img = new Image
                    }

                    return s.prototype.addElementBackgroundImages = function (e) {
                        var t = getComputedStyle(e);
                        if (t) for (var n = /url\((['"])?(.*?)\1\)/gi, i = n.exec(t.backgroundImage); null !== i;) {
                            var o = i && i[2];
                            o && this.addBackground(o, e), i = n.exec(t.backgroundImage)
                        }
                    }, s.prototype.addImage = function (e) {
                        var t = new l(e);
                        this.images.push(t)
                    }, s.prototype.addBackground = function (e, t) {
                        var n = new c(e, t);
                        this.images.push(n)
                    }, s.prototype.check = function () {
                        var e = this;

                        function t(t, n, i) {
                            setTimeout(function () {
                                e.progress(t, n, i)
                            })
                        }

                        this.progressedCount = 0, this.hasAnyBroken = !1, this.images.length ? this.images.forEach(function (e) {
                            e.once("progress", t), e.check()
                        }) : this.complete()
                    }, s.prototype.progress = function (e, t, n) {
                        this.progressedCount++, this.hasAnyBroken = this.hasAnyBroken || !e.isLoaded, this.emitEvent("progress", [this, e, t]), this.jqDeferred && this.jqDeferred.notify && this.jqDeferred.notify(this, e), this.progressedCount == this.images.length && this.complete(), this.options.debug && i && i.log("progress: " + n, e, t)
                    }, s.prototype.complete = function () {
                        var e = this.hasAnyBroken ? "fail" : "done";
                        if (this.isComplete = !0, this.emitEvent(e, [this]), this.emitEvent("always", [this]), this.jqDeferred) {
                            var t = this.hasAnyBroken ? "reject" : "resolve";
                            this.jqDeferred[t](this)
                        }
                    }, l.prototype = Object.create(t.prototype), l.prototype.check = function () {
                        this.getIsImageComplete() ? this.confirm(0 !== this.img.naturalWidth, "naturalWidth") : (this.proxyImage = new Image, this.proxyImage.addEventListener("load", this), this.proxyImage.addEventListener("error", this), this.img.addEventListener("load", this), this.img.addEventListener("error", this), this.proxyImage.src = this.img.src)
                    }, l.prototype.getIsImageComplete = function () {
                        return this.img.complete && this.img.naturalWidth
                    }, l.prototype.confirm = function (e, t) {
                        this.isLoaded = e, this.emitEvent("progress", [this, this.img, t])
                    }, l.prototype.handleEvent = function (e) {
                        var t = "on" + e.type;
                        this[t] && this[t](e)
                    }, l.prototype.onload = function () {
                        this.confirm(!0, "onload"), this.unbindEvents()
                    }, l.prototype.onerror = function () {
                        this.confirm(!1, "onerror"), this.unbindEvents()
                    }, l.prototype.unbindEvents = function () {
                        this.proxyImage.removeEventListener("load", this), this.proxyImage.removeEventListener("error", this), this.img.removeEventListener("load", this), this.img.removeEventListener("error", this)
                    }, c.prototype = Object.create(l.prototype), c.prototype.check = function () {
                        this.img.addEventListener("load", this), this.img.addEventListener("error", this), this.img.src = this.url, this.getIsImageComplete() && (this.confirm(0 !== this.img.naturalWidth, "naturalWidth"), this.unbindEvents())
                    }, c.prototype.unbindEvents = function () {
                        this.img.removeEventListener("load", this), this.img.removeEventListener("error", this)
                    }, c.prototype.confirm = function (e, t) {
                        this.isLoaded = e, this.emitEvent("progress", [this, this.element, t])
                    }, s.makeJQueryPlugin = function (t) {
                        (t = t || e.jQuery) && ((n = t).fn.imagesLoaded = function (e, t) {
                            return new s(this, e, t).jqDeferred.promise(n(this))
                        })
                    }, s.makeJQueryPlugin(), s
                }(n, e)
            }.apply(t, [i])) || (e.exports = s)
        }("undefined" != typeof window ? window : this)
}, function (e, t, n) {
    e.exports = function () {
        "use strict";

        function e() {
        }

        e.prototype = {
            on: function (e, t, n) {
                var i = this.e || (this.e = {});
                return (i[e] || (i[e] = [])).push({fn: t, ctx: n}), this
            }, once: function (e, t, n) {
                var i = this;

                function o() {
                    i.off(e, o), t.apply(n, arguments)
                }

                return o._ = t, this.on(e, o, n)
            }, emit: function (e) {
                for (var t = [].slice.call(arguments, 1), n = ((this.e || (this.e = {}))[e] || []).slice(), i = 0, o = n.length; i < o; i++) n[i].fn.apply(n[i].ctx, t);
                return this
            }, off: function (e, t) {
                var n = this.e || (this.e = {}), i = n[e], o = [];
                if (i && t) for (var r = 0, s = i.length; r < s; r++) i[r].fn !== t && i[r].fn._ !== t && o.push(i[r]);
                return o.length ? n[e] = o : delete n[e], this
            }
        };
        var t = e, n = "undefined" != typeof Element ? Element.prototype : {},
            i = n.matches || n.matchesSelector || n.webkitMatchesSelector || n.mozMatchesSelector || n.msMatchesSelector || n.oMatchesSelector;

        function o() {
        }

        function r(e) {
            return parseFloat(e) || 0
        }

        var s = function (e, t) {
            if (!(e instanceof t)) throw new TypeError("Cannot call a class as a function")
        }, a = function () {
            function e(e, t) {
                for (var n = 0; n < t.length; n++) {
                    var i = t[n];
                    i.enumerable = i.enumerable || !1, i.configurable = !0, "value" in i && (i.writable = !0), Object.defineProperty(e, i.key, i)
                }
            }

            return function (t, n, i) {
                return n && e(t.prototype, n), i && e(t, i), t
            }
        }(), l = function (e, t) {
            if (!e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
            return !t || "object" != typeof t && "function" != typeof t ? e : t
        }, c = function () {
            function e(t, n) {
                s(this, e), this.x = r(t), this.y = r(n)
            }

            return a(e, null, [{
                key: "equals", value: function (e, t) {
                    return e.x === t.x && e.y === t.y
                }
            }]), e
        }(), u = function () {
            function e(t, n, i, o, r) {
                s(this, e), this.id = r, this.left = t, this.top = n, this.width = i, this.height = o
            }

            return a(e, null, [{
                key: "intersects", value: function (e, t) {
                    return e.left < t.left + t.width && t.left < e.left + e.width && e.top < t.top + t.height && t.top < e.top + e.height
                }
            }]), e
        }(), d = {
            BASE: "shuffle",
            SHUFFLE_ITEM: "shuffle-item",
            VISIBLE: "shuffle-item--visible",
            HIDDEN: "shuffle-item--hidden"
        }, p = 0, f = function () {
            function e(t) {
                s(this, e), p += 1, this.id = p, this.element = t, this.isVisible = !0, this.isHidden = !1
            }

            return a(e, [{
                key: "show", value: function () {
                    this.isVisible = !0, this.element.classList.remove(d.HIDDEN), this.element.classList.add(d.VISIBLE), this.element.removeAttribute("aria-hidden")
                }
            }, {
                key: "hide", value: function () {
                    this.isVisible = !1, this.element.classList.remove(d.VISIBLE), this.element.classList.add(d.HIDDEN), this.element.setAttribute("aria-hidden", !0)
                }
            }, {
                key: "init", value: function () {
                    this.addClasses([d.SHUFFLE_ITEM, d.VISIBLE]), this.applyCss(e.Css.INITIAL), this.scale = e.Scale.VISIBLE, this.point = new c
                }
            }, {
                key: "addClasses", value: function (e) {
                    var t = this;
                    e.forEach(function (e) {
                        t.element.classList.add(e)
                    })
                }
            }, {
                key: "removeClasses", value: function (e) {
                    var t = this;
                    e.forEach(function (e) {
                        t.element.classList.remove(e)
                    })
                }
            }, {
                key: "applyCss", value: function (e) {
                    var t = this;
                    Object.keys(e).forEach(function (n) {
                        t.element.style[n] = e[n]
                    })
                }
            }, {
                key: "dispose", value: function () {
                    this.removeClasses([d.HIDDEN, d.VISIBLE, d.SHUFFLE_ITEM]), this.element.removeAttribute("style"), this.element = null
                }
            }]), e
        }();
        f.Css = {
            INITIAL: {position: "absolute", top: 0, left: 0, visibility: "visible", "will-change": "transform"},
            VISIBLE: {before: {opacity: 1, visibility: "visible"}, after: {transitionDelay: ""}},
            HIDDEN: {before: {opacity: 0}, after: {visibility: "hidden", transitionDelay: ""}}
        }, f.Scale = {VISIBLE: 1, HIDDEN: .001};
        var h = document.body || document.documentElement, m = document.createElement("div");
        m.style.cssText = "width:10px;padding:2px;box-sizing:border-box;", h.appendChild(m);
        var g = "10px" === window.getComputedStyle(m, null).width;

        function v(e, t) {
            var n = arguments.length > 2 && void 0 !== arguments[2] ? arguments[2] : window.getComputedStyle(e, null),
                i = r(n[t]);
            return g || "width" !== t ? g || "height" !== t || (i += r(n.paddingTop) + r(n.paddingBottom) + r(n.borderTopWidth) + r(n.borderBottomWidth)) : i += r(n.paddingLeft) + r(n.paddingRight) + r(n.borderLeftWidth) + r(n.borderRightWidth), i
        }

        h.removeChild(m);
        var y = {reverse: !1, by: null, compare: null, randomize: !1, key: "element"};

        function b(e, t) {
            var n = Object.assign({}, y, t), i = Array.from(e), o = !1;
            return e.length ? n.randomize ? function (e) {
                for (var t = e.length; t;) {
                    t -= 1;
                    var n = Math.floor(Math.random() * (t + 1)), i = e[n];
                    e[n] = e[t], e[t] = i
                }
                return e
            }(e) : ("function" == typeof n.by ? e.sort(function (e, t) {
                if (o) return 0;
                var i = n.by(e[n.key]), r = n.by(t[n.key]);
                return void 0 === i && void 0 === r ? (o = !0, 0) : i < r || "sortFirst" === i || "sortLast" === r ? -1 : i > r || "sortLast" === i || "sortFirst" === r ? 1 : 0
            }) : "function" == typeof n.compare && e.sort(n.compare), o ? i : (n.reverse && e.reverse(), e)) : []
        }

        var w = {}, x = "transitionend", T = 0;

        function S(e) {
            return !!w[e] && (w[e].element.removeEventListener(x, w[e].listener), w[e] = null, !0)
        }

        function C(e) {
            return Math.max.apply(Math, e)
        }

        function E(e, t, n, i) {
            var o = e / t;
            return Math.abs(Math.round(o) - o) < i && (o = Math.round(o)), Math.min(Math.ceil(o), n)
        }

        function k(e, t, n) {
            if (1 === t) return e;
            for (var i = [], o = 0; o <= n - t; o++) i.push(C(e.slice(o, o + t)));
            return i
        }

        function _(e, t) {
            for (var n, i = (n = e, Math.min.apply(Math, n)), o = 0, r = e.length; o < r; o++) if (e[o] >= i - t && e[o] <= i + t) return o;
            return 0
        }

        function A(e, t) {
            var n = {};
            e.forEach(function (e) {
                n[e.top] ? n[e.top].push(e) : n[e.top] = [e]
            });
            var i = [], o = [], r = [];
            return Object.keys(n).forEach(function (e) {
                var s = n[e];
                o.push(s);
                var a = s[s.length - 1], l = a.left + a.width, c = Math.round((t - l) / 2), d = s, p = !1;
                if (c > 0) {
                    var f = [];
                    (p = s.every(function (e) {
                        var t = new u(e.left + c, e.top, e.width, e.height, e.id), n = !i.some(function (e) {
                            return u.intersects(t, e)
                        });
                        return f.push(t), n
                    })) && (d = f)
                }
                if (!p) {
                    var h = void 0;
                    if (s.some(function (e) {
                        return i.some(function (t) {
                            var n = u.intersects(e, t);
                            return n && (h = t), n
                        })
                    })) {
                        var m = r.findIndex(function (e) {
                            return e.includes(h)
                        });
                        r.splice(m, 1, o[m])
                    }
                }
                i = i.concat(d), r.push(d)
            }), [].concat.apply([], r).sort(function (e, t) {
                return e.id - t.id
            }).map(function (e) {
                return new c(e.left, e.top)
            })
        }

        function I(e) {
            return Array.from(new Set(e))
        }

        var O = 0, D = function (e) {
            function n(e) {
                var t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {};
                s(this, n);
                var i = l(this, (n.__proto__ || Object.getPrototypeOf(n)).call(this));
                i.options = Object.assign({}, n.options, t), i.options.delimeter && (i.options.delimiter = i.options.delimeter), i.lastSort = {}, i.group = n.ALL_ITEMS, i.lastFilter = n.ALL_ITEMS, i.isEnabled = !0, i.isDestroyed = !1, i.isInitialized = !1, i._transitions = [], i.isTransitioning = !1, i._queue = [];
                var o = i._getElementOption(e);
                if (!o) throw new TypeError("Shuffle needs to be initialized with an element.");
                return i.element = o, i.id = "shuffle_" + O, O += 1, i._init(), i.isInitialized = !0, i
            }

            return function (e, t) {
                if ("function" != typeof t && null !== t) throw new TypeError("Super expression must either be null or a function, not " + typeof t);
                e.prototype = Object.create(t && t.prototype, {
                    constructor: {
                        value: e,
                        enumerable: !1,
                        writable: !0,
                        configurable: !0
                    }
                }), t && (Object.setPrototypeOf ? Object.setPrototypeOf(e, t) : e.__proto__ = t)
            }(n, t), a(n, [{
                key: "_init", value: function () {
                    if (this.items = this._getItems(), this.options.sizer = this._getElementOption(this.options.sizer), this.element.classList.add(n.Classes.BASE), this._initItems(this.items), this._onResize = this._getResizeFunction(), window.addEventListener("resize", this._onResize), "complete" !== document.readyState) {
                        var e = this.layout.bind(this);
                        window.addEventListener("load", function t() {
                            window.removeEventListener("load", t), e()
                        })
                    }
                    var t = window.getComputedStyle(this.element, null), i = n.getSize(this.element).width;
                    this._validateStyles(t), this._setColumns(i), this.filter(this.options.group, this.options.initialSort), this.element.offsetWidth, this.setItemTransitions(this.items), this.element.style.transition = "height " + this.options.speed + "ms " + this.options.easing
                }
            }, {
                key: "_getResizeFunction", value: function () {
                    var e = this._handleResize.bind(this);
                    return this.options.throttle ? this.options.throttle(e, this.options.throttleTime) : e
                }
            }, {
                key: "_getElementOption", value: function (e) {
                    return "string" == typeof e ? this.element.querySelector(e) : e && e.nodeType && 1 === e.nodeType ? e : e && e.jquery ? e[0] : null
                }
            }, {
                key: "_validateStyles", value: function (e) {
                    "static" === e.position && (this.element.style.position = "relative"), "hidden" !== e.overflow && (this.element.style.overflow = "hidden")
                }
            }, {
                key: "_filter", value: function () {
                    var e = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : this.lastFilter,
                        t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : this.items,
                        n = this._getFilteredSets(e, t);
                    return this._toggleFilterClasses(n), this.lastFilter = e, "string" == typeof e && (this.group = e), n
                }
            }, {
                key: "_getFilteredSets", value: function (e, t) {
                    var i = this, o = [], r = [];
                    return e === n.ALL_ITEMS ? o = t : t.forEach(function (t) {
                        i._doesPassFilter(e, t.element) ? o.push(t) : r.push(t)
                    }), {visible: o, hidden: r}
                }
            }, {
                key: "_doesPassFilter", value: function (e, t) {
                    if ("function" == typeof e) return e.call(t, t, this);
                    var i = t.getAttribute("data-" + n.FILTER_ATTRIBUTE_KEY),
                        o = this.options.delimiter ? i.split(this.options.delimiter) : JSON.parse(i);

                    function r(e) {
                        return o.includes(e)
                    }

                    return Array.isArray(e) ? this.options.filterMode === n.FilterMode.ANY ? e.some(r) : e.every(r) : o.includes(e)
                }
            }, {
                key: "_toggleFilterClasses", value: function (e) {
                    var t = e.visible, n = e.hidden;
                    t.forEach(function (e) {
                        e.show()
                    }), n.forEach(function (e) {
                        e.hide()
                    })
                }
            }, {
                key: "_initItems", value: function (e) {
                    e.forEach(function (e) {
                        e.init()
                    })
                }
            }, {
                key: "_disposeItems", value: function (e) {
                    e.forEach(function (e) {
                        e.dispose()
                    })
                }
            }, {
                key: "_updateItemCount", value: function () {
                    this.visibleItems = this._getFilteredItems().length
                }
            }, {
                key: "setItemTransitions", value: function (e) {
                    var t = this.options, n = t.speed, i = t.easing,
                        o = this.options.useTransforms ? ["transform"] : ["top", "left"],
                        r = Object.keys(f.Css.HIDDEN.before).map(function (e) {
                            return e.replace(/([A-Z])/g, function (e, t) {
                                return "-" + t.toLowerCase()
                            })
                        }), s = o.concat(r).join();
                    e.forEach(function (e) {
                        e.element.style.transitionDuration = n + "ms", e.element.style.transitionTimingFunction = i, e.element.style.transitionProperty = s
                    })
                }
            }, {
                key: "_getItems", value: function () {
                    var e = this;
                    return Array.from(this.element.children).filter(function (t) {
                        return function (e, t) {
                            if (!e || 1 !== e.nodeType) return !1;
                            if (i) return i.call(e, t);
                            for (var n = e.parentNode.querySelectorAll(t), o = 0; o < n.length; o++) if (n[o] == e) return !0;
                            return !1
                        }(t, e.options.itemSelector)
                    }).map(function (e) {
                        return new f(e)
                    })
                }
            }, {
                key: "_mergeNewItems", value: function (e) {
                    var t = Array.from(this.element.children);
                    return b(this.items.concat(e), {
                        by: function (e) {
                            return t.indexOf(e)
                        }
                    })
                }
            }, {
                key: "_getFilteredItems", value: function () {
                    return this.items.filter(function (e) {
                        return e.isVisible
                    })
                }
            }, {
                key: "_getConcealedItems", value: function () {
                    return this.items.filter(function (e) {
                        return !e.isVisible
                    })
                }
            }, {
                key: "_getColumnSize", value: function (e, t) {
                    var i = void 0;
                    return 0 === (i = "function" == typeof this.options.columnWidth ? this.options.columnWidth(e) : this.options.sizer ? n.getSize(this.options.sizer).width : this.options.columnWidth ? this.options.columnWidth : this.items.length > 0 ? n.getSize(this.items[0].element, !0).width : e) && (i = e), i + t
                }
            }, {
                key: "_getGutterSize", value: function (e) {
                    return "function" == typeof this.options.gutterWidth ? this.options.gutterWidth(e) : this.options.sizer ? v(this.options.sizer, "marginLeft") : this.options.gutterWidth
                }
            }, {
                key: "_setColumns", value: function () {
                    var e = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : n.getSize(this.element).width,
                        t = this._getGutterSize(e), i = this._getColumnSize(e, t), o = (e + t) / i;
                    Math.abs(Math.round(o) - o) < this.options.columnThreshold && (o = Math.round(o)), this.cols = Math.max(Math.floor(o), 1), this.containerWidth = e, this.colWidth = i
                }
            }, {
                key: "_setContainerSize", value: function () {
                    this.element.style.height = this._getContainerSize() + "px"
                }
            }, {
                key: "_getContainerSize", value: function () {
                    return C(this.positions)
                }
            }, {
                key: "_getStaggerAmount", value: function (e) {
                    return Math.min(e * this.options.staggerAmount, this.options.staggerAmountMax)
                }
            }, {
                key: "_dispatch", value: function (e) {
                    var t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {};
                    this.isDestroyed || (t.shuffle = this, this.emit(e, t))
                }
            }, {
                key: "_resetCols", value: function () {
                    var e = this.cols;
                    for (this.positions = []; e;) e -= 1, this.positions.push(0)
                }
            }, {
                key: "_layout", value: function (e) {
                    var t = this, n = this._getNextPositions(e), i = 0;
                    e.forEach(function (e, o) {
                        function r() {
                            e.applyCss(f.Css.VISIBLE.after)
                        }

                        if (c.equals(e.point, n[o]) && !e.isHidden) return e.applyCss(f.Css.VISIBLE.before), void r();
                        e.point = n[o], e.scale = f.Scale.VISIBLE, e.isHidden = !1;
                        var s = t.getStylesForTransition(e, f.Css.VISIBLE.before);
                        s.transitionDelay = t._getStaggerAmount(i) + "ms", t._queue.push({
                            item: e,
                            styles: s,
                            callback: r
                        }), i += 1
                    })
                }
            }, {
                key: "_getNextPositions", value: function (e) {
                    var t = this;
                    if (this.options.isCentered) {
                        var i = e.map(function (e, i) {
                            var o = n.getSize(e.element, !0), r = t._getItemPosition(o);
                            return new u(r.x, r.y, o.width, o.height, i)
                        });
                        return this.getTransformedPositions(i, this.containerWidth)
                    }
                    return e.map(function (e) {
                        return t._getItemPosition(n.getSize(e.element, !0))
                    })
                }
            }, {
                key: "_getItemPosition", value: function (e) {
                    return function (e) {
                        for (var t = e.itemSize, n = e.positions, i = e.gridSize, o = e.total, r = e.threshold, s = e.buffer, a = E(t.width, i, o, r), l = k(n, a, o), u = _(l, s), d = new c(i * u, l[u]), p = l[u] + t.height, f = 0; f < a; f++) n[u + f] = p;
                        return d
                    }({
                        itemSize: e,
                        positions: this.positions,
                        gridSize: this.colWidth,
                        total: this.cols,
                        threshold: this.options.columnThreshold,
                        buffer: this.options.buffer
                    })
                }
            }, {
                key: "getTransformedPositions", value: function (e, t) {
                    return A(e, t)
                }
            }, {
                key: "_shrink", value: function () {
                    var e = this, t = 0;
                    (arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : this._getConcealedItems()).forEach(function (n) {
                        function i() {
                            n.applyCss(f.Css.HIDDEN.after)
                        }

                        if (n.isHidden) return n.applyCss(f.Css.HIDDEN.before), void i();
                        n.scale = f.Scale.HIDDEN, n.isHidden = !0;
                        var o = e.getStylesForTransition(n, f.Css.HIDDEN.before);
                        o.transitionDelay = e._getStaggerAmount(t) + "ms", e._queue.push({
                            item: n,
                            styles: o,
                            callback: i
                        }), t += 1
                    })
                }
            }, {
                key: "_handleResize", value: function () {
                    this.isEnabled && !this.isDestroyed && this.update()
                }
            }, {
                key: "getStylesForTransition", value: function (e, t) {
                    var n = Object.assign({}, t);
                    if (this.options.useTransforms) {
                        var i = this.options.roundTransforms ? Math.round(e.point.x) : e.point.x,
                            o = this.options.roundTransforms ? Math.round(e.point.y) : e.point.y;
                        n.transform = "translate(" + i + "px, " + o + "px) scale(" + e.scale + ")"
                    } else n.left = e.point.x + "px", n.top = e.point.y + "px";
                    return n
                }
            }, {
                key: "_whenTransitionDone", value: function (e, t, n) {
                    var i = function (e, t) {
                        var n = x + (T += 1), i = function (e) {
                            e.currentTarget === e.target && (S(n), t(e))
                        };
                        return e.addEventListener(x, i), w[n] = {element: e, listener: i}, n
                    }(e, function (e) {
                        t(), n(null, e)
                    });
                    this._transitions.push(i)
                }
            }, {
                key: "_getTransitionFunction", value: function (e) {
                    var t = this;
                    return function (n) {
                        e.item.applyCss(e.styles), t._whenTransitionDone(e.item.element, e.callback, n)
                    }
                }
            }, {
                key: "_processQueue", value: function () {
                    this.isTransitioning && this._cancelMovement();
                    var e = this.options.speed > 0, t = this._queue.length > 0;
                    t && e && this.isInitialized ? this._startTransitions(this._queue) : t ? (this._styleImmediately(this._queue), this._dispatch(n.EventType.LAYOUT)) : this._dispatch(n.EventType.LAYOUT), this._queue.length = 0
                }
            }, {
                key: "_startTransitions", value: function (e) {
                    var t = this;
                    this.isTransitioning = !0, function (e, t, n) {
                        n || ("function" == typeof t ? (n = t, t = null) : n = o);
                        var i = e && e.length;
                        if (!i) return n(null, []);
                        var r = !1, s = new Array(i);

                        function a(e) {
                            return function (t, o) {
                                if (!r) {
                                    if (t) return n(t, s), void (r = !0);
                                    s[e] = o, --i || n(null, s)
                                }
                            }
                        }

                        e.forEach(t ? function (e, n) {
                            e.call(t, a(n))
                        } : function (e, t) {
                            e(a(t))
                        })
                    }(e.map(function (e) {
                        return t._getTransitionFunction(e)
                    }), this._movementFinished.bind(this))
                }
            }, {
                key: "_cancelMovement", value: function () {
                    this._transitions.forEach(S), this._transitions.length = 0, this.isTransitioning = !1
                }
            }, {
                key: "_styleImmediately", value: function (e) {
                    if (e.length) {
                        var t = e.map(function (e) {
                            return e.item.element
                        });
                        n._skipTransitions(t, function () {
                            e.forEach(function (e) {
                                e.item.applyCss(e.styles), e.callback()
                            })
                        })
                    }
                }
            }, {
                key: "_movementFinished", value: function () {
                    this._transitions.length = 0, this.isTransitioning = !1, this._dispatch(n.EventType.LAYOUT)
                }
            }, {
                key: "filter", value: function (e, t) {
                    this.isEnabled && ((!e || e && 0 === e.length) && (e = n.ALL_ITEMS), this._filter(e), this._shrink(), this._updateItemCount(), this.sort(t))
                }
            }, {
                key: "sort", value: function () {
                    var e = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : this.lastSort;
                    if (this.isEnabled) {
                        this._resetCols();
                        var t = b(this._getFilteredItems(), e);
                        this._layout(t), this._processQueue(), this._setContainerSize(), this.lastSort = e
                    }
                }
            }, {
                key: "update", value: function () {
                    var e = arguments.length > 0 && void 0 !== arguments[0] && arguments[0];
                    this.isEnabled && (e || this._setColumns(), this.sort())
                }
            }, {
                key: "layout", value: function () {
                    this.update(!0)
                }
            }, {
                key: "add", value: function (e) {
                    var t = this, n = I(e).map(function (e) {
                        return new f(e)
                    });
                    this._initItems(n), this._resetCols();
                    var i = b(this._mergeNewItems(n), this.lastSort), o = this._filter(this.lastFilter, i),
                        r = function (e) {
                            return n.includes(e)
                        }, s = function (e) {
                            e.scale = f.Scale.HIDDEN, e.isHidden = !0, e.applyCss(f.Css.HIDDEN.before), e.applyCss(f.Css.HIDDEN.after)
                        }, a = this._getNextPositions(o.visible);
                    o.visible.forEach(function (e, n) {
                        r(e) && (e.point = a[n], s(e), e.applyCss(t.getStylesForTransition(e, {})))
                    }), o.hidden.forEach(function (e) {
                        r(e) && s(e)
                    }), this.element.offsetWidth, this.setItemTransitions(n), this.items = this._mergeNewItems(n), this.filter(this.lastFilter)
                }
            }, {
                key: "disable", value: function () {
                    this.isEnabled = !1
                }
            }, {
                key: "enable", value: function () {
                    var e = !(arguments.length > 0 && void 0 !== arguments[0]) || arguments[0];
                    this.isEnabled = !0, e && this.update()
                }
            }, {
                key: "remove", value: function (e) {
                    var t = this;
                    if (e.length) {
                        var i = I(e), o = i.map(function (e) {
                            return t.getItemByElement(e)
                        }).filter(function (e) {
                            return !!e
                        });
                        this._toggleFilterClasses({
                            visible: [],
                            hidden: o
                        }), this._shrink(o), this.sort(), this.items = this.items.filter(function (e) {
                            return !o.includes(e)
                        }), this._updateItemCount(), this.once(n.EventType.LAYOUT, function () {
                            t._disposeItems(o), i.forEach(function (e) {
                                e.parentNode.removeChild(e)
                            }), t._dispatch(n.EventType.REMOVED, {collection: i})
                        })
                    }
                }
            }, {
                key: "getItemByElement", value: function (e) {
                    return this.items.find(function (t) {
                        return t.element === e
                    })
                }
            }, {
                key: "resetItems", value: function () {
                    var e = this;
                    this._disposeItems(this.items), this.isInitialized = !1, this.items = this._getItems(), this._initItems(this.items), this.once(n.EventType.LAYOUT, function () {
                        e.setItemTransitions(e.items), e.isInitialized = !0
                    }), this.filter(this.lastFilter)
                }
            }, {
                key: "destroy", value: function () {
                    this._cancelMovement(), window.removeEventListener("resize", this._onResize), this.element.classList.remove("shuffle"), this.element.removeAttribute("style"), this._disposeItems(this.items), this.items.length = 0, this._transitions.length = 0, this.options.sizer = null, this.element = null, this.isDestroyed = !0, this.isEnabled = !1
                }
            }], [{
                key: "getSize", value: function (e) {
                    var t = arguments.length > 1 && void 0 !== arguments[1] && arguments[1],
                        n = window.getComputedStyle(e, null), i = v(e, "width", n), o = v(e, "height", n);
                    return t && (i += v(e, "marginLeft", n) + v(e, "marginRight", n), o += v(e, "marginTop", n) + v(e, "marginBottom", n)), {
                        width: i,
                        height: o
                    }
                }
            }, {
                key: "_skipTransitions", value: function (e, t) {
                    var n = e.map(function (e) {
                        var t = e.style, n = t.transitionDuration, i = t.transitionDelay;
                        return t.transitionDuration = "0ms", t.transitionDelay = "0ms", {duration: n, delay: i}
                    });
                    t(), e[0].offsetWidth, e.forEach(function (e, t) {
                        e.style.transitionDuration = n[t].duration, e.style.transitionDelay = n[t].delay
                    })
                }
            }]), n
        }();
        return D.ShuffleItem = f, D.ALL_ITEMS = "all", D.FILTER_ATTRIBUTE_KEY = "groups", D.EventType = {
            LAYOUT: "shuffle:layout",
            REMOVED: "shuffle:removed"
        }, D.Classes = d, D.FilterMode = {ANY: "any", ALL: "all"}, D.options = {
            group: D.ALL_ITEMS,
            speed: 250,
            easing: "cubic-bezier(0.4, 0.0, 0.2, 1)",
            itemSelector: "*",
            sizer: null,
            gutterWidth: 0,
            columnWidth: 0,
            delimiter: null,
            buffer: 0,
            columnThreshold: .01,
            initialSort: null,
            throttle: function (e, t) {
                var n, i, o, r, s = 0;
                return function () {
                    n = this, i = arguments;
                    var e = new Date - s;
                    return r || (e >= t ? a() : r = setTimeout(a, t - e)), o
                };

                function a() {
                    r = 0, s = +new Date, o = e.apply(n, i), n = null, i = null
                }
            },
            throttleTime: 300,
            staggerAmount: 15,
            staggerAmountMax: 150,
            useTransforms: !0,
            filterMode: D.FilterMode.ANY,
            isCentered: !1,
            roundTransforms: !0
        }, D.Point = c, D.Rect = u, D.__sorter = b, D.__getColumnSpan = E, D.__getAvailablePositions = k, D.__getShortColumn = _, D.__getCenteredPositions = A, D
    }()
}, function (e, t, n) {
    n(60), function (e) {
        page.registerVendor("Slick"), page.initSlick = function () {
            e('[data-provide~="slider"]').each(function () {
                var t = e(this), n = {speed: 1e3, arrows: !1, centerPadding: "0"};
                if (void 0 !== (n = e.extend(n, page.getDataOptions(t))).slidesToShow || void 0 !== n.centerMode) {
                    var i = 1;
                    void 0 !== n.slidesToScroll && n.slidesToScroll > 1 && (i = 2), n.responsive = [{
                        breakpoint: 768,
                        settings: {slidesToShow: 2, slidesToScroll: i}
                    }, {breakpoint: 576, settings: {slidesToShow: 1, slidesToScroll: 1, centerPadding: "0px"}}]
                }
                t.slick(n)
            })
        }
    }(jQuery)
}, function (e, t, n) {
    var i, o, r;
    !function (s) {
        "use strict";
        o = [n(3)], void 0 === (r = "function" == typeof (i = function (e) {
            var t = window.Slick || {};
            (t = function () {
                var t = 0;
                return function (n, i) {
                    var o, r = this;
                    r.defaults = {
                        accessibility: !0,
                        adaptiveHeight: !1,
                        appendArrows: e(n),
                        appendDots: e(n),
                        arrows: !0,
                        asNavFor: null,
                        prevArrow: '<button class="slick-prev" aria-label="Previous" type="button">Previous</button>',
                        nextArrow: '<button class="slick-next" aria-label="Next" type="button">Next</button>',
                        autoplay: !1,
                        autoplaySpeed: 3e3,
                        centerMode: !1,
                        centerPadding: "50px",
                        cssEase: "ease",
                        customPaging: function (t, n) {
                            return e('<button type="button" />').text(n + 1)
                        },
                        dots: !1,
                        dotsClass: "slick-dots",
                        draggable: !0,
                        easing: "linear",
                        edgeFriction: .35,
                        fade: !1,
                        focusOnSelect: !1,
                        focusOnChange: !1,
                        infinite: !0,
                        initialSlide: 0,
                        lazyLoad: "ondemand",
                        mobileFirst: !1,
                        pauseOnHover: !0,
                        pauseOnFocus: !0,
                        pauseOnDotsHover: !1,
                        respondTo: "window",
                        responsive: null,
                        rows: 1,
                        rtl: !1,
                        slide: "",
                        slidesPerRow: 1,
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        speed: 500,
                        swipe: !0,
                        swipeToSlide: !1,
                        touchMove: !0,
                        touchThreshold: 5,
                        useCSS: !0,
                        useTransform: !0,
                        variableWidth: !1,
                        vertical: !1,
                        verticalSwiping: !1,
                        waitForAnimate: !0,
                        zIndex: 1e3
                    }, r.initials = {
                        animating: !1,
                        dragging: !1,
                        autoPlayTimer: null,
                        currentDirection: 0,
                        currentLeft: null,
                        currentSlide: 0,
                        direction: 1,
                        $dots: null,
                        listWidth: null,
                        listHeight: null,
                        loadIndex: 0,
                        $nextArrow: null,
                        $prevArrow: null,
                        scrolling: !1,
                        slideCount: null,
                        slideWidth: null,
                        $slideTrack: null,
                        $slides: null,
                        sliding: !1,
                        slideOffset: 0,
                        swipeLeft: null,
                        swiping: !1,
                        $list: null,
                        touchObject: {},
                        transformsEnabled: !1,
                        unslicked: !1
                    }, e.extend(r, r.initials), r.activeBreakpoint = null, r.animType = null, r.animProp = null, r.breakpoints = [], r.breakpointSettings = [], r.cssTransitions = !1, r.focussed = !1, r.interrupted = !1, r.hidden = "hidden", r.paused = !0, r.positionProp = null, r.respondTo = null, r.rowCount = 1, r.shouldClick = !0, r.$slider = e(n), r.$slidesCache = null, r.transformType = null, r.transitionType = null, r.visibilityChange = "visibilitychange", r.windowWidth = 0, r.windowTimer = null, o = e(n).data("slick") || {}, r.options = e.extend({}, r.defaults, i, o), r.currentSlide = r.options.initialSlide, r.originalSettings = r.options, void 0 !== document.mozHidden ? (r.hidden = "mozHidden", r.visibilityChange = "mozvisibilitychange") : void 0 !== document.webkitHidden && (r.hidden = "webkitHidden", r.visibilityChange = "webkitvisibilitychange");
                    r.autoPlay = e.proxy(r.autoPlay, r), r.autoPlayClear = e.proxy(r.autoPlayClear, r), r.autoPlayIterator = e.proxy(r.autoPlayIterator, r), r.changeSlide = e.proxy(r.changeSlide, r), r.clickHandler = e.proxy(r.clickHandler, r), r.selectHandler = e.proxy(r.selectHandler, r), r.setPosition = e.proxy(r.setPosition, r), r.swipeHandler = e.proxy(r.swipeHandler, r), r.dragHandler = e.proxy(r.dragHandler, r), r.keyHandler = e.proxy(r.keyHandler, r), r.instanceUid = t++, r.htmlExpr = /^(?:\s*(<[\w\W]+>)[^>]*)$/, r.registerBreakpoints(), r.init(!0)
                }
            }()).prototype.activateADA = function () {
                this.$slideTrack.find(".slick-active").attr({"aria-hidden": "false"}).find("a, input, button, select").attr({tabindex: "0"})
            }, t.prototype.addSlide = t.prototype.slickAdd = function (t, n, i) {
                var o = this;
                if ("boolean" == typeof n) i = n, n = null; else if (n < 0 || n >= o.slideCount) return !1;
                o.unload(), "number" == typeof n ? 0 === n && 0 === o.$slides.length ? e(t).appendTo(o.$slideTrack) : i ? e(t).insertBefore(o.$slides.eq(n)) : e(t).insertAfter(o.$slides.eq(n)) : !0 === i ? e(t).prependTo(o.$slideTrack) : e(t).appendTo(o.$slideTrack), o.$slides = o.$slideTrack.children(this.options.slide), o.$slideTrack.children(this.options.slide).detach(), o.$slideTrack.append(o.$slides), o.$slides.each(function (t, n) {
                    e(n).attr("data-slick-index", t)
                }), o.$slidesCache = o.$slides, o.reinit()
            }, t.prototype.animateHeight = function () {
                var e = this;
                if (1 === e.options.slidesToShow && !0 === e.options.adaptiveHeight && !1 === e.options.vertical) {
                    var t = e.$slides.eq(e.currentSlide).outerHeight(!0);
                    e.$list.animate({height: t}, e.options.speed)
                }
            }, t.prototype.animateSlide = function (t, n) {
                var i = {}, o = this;
                o.animateHeight(), !0 === o.options.rtl && !1 === o.options.vertical && (t = -t), !1 === o.transformsEnabled ? !1 === o.options.vertical ? o.$slideTrack.animate({left: t}, o.options.speed, o.options.easing, n) : o.$slideTrack.animate({top: t}, o.options.speed, o.options.easing, n) : !1 === o.cssTransitions ? (!0 === o.options.rtl && (o.currentLeft = -o.currentLeft), e({animStart: o.currentLeft}).animate({animStart: t}, {
                    duration: o.options.speed,
                    easing: o.options.easing,
                    step: function (e) {
                        e = Math.ceil(e), !1 === o.options.vertical ? (i[o.animType] = "translate(" + e + "px, 0px)", o.$slideTrack.css(i)) : (i[o.animType] = "translate(0px," + e + "px)", o.$slideTrack.css(i))
                    },
                    complete: function () {
                        n && n.call()
                    }
                })) : (o.applyTransition(), t = Math.ceil(t), !1 === o.options.vertical ? i[o.animType] = "translate3d(" + t + "px, 0px, 0px)" : i[o.animType] = "translate3d(0px," + t + "px, 0px)", o.$slideTrack.css(i), n && setTimeout(function () {
                    o.disableTransition(), n.call()
                }, o.options.speed))
            }, t.prototype.getNavTarget = function () {
                var t = this.options.asNavFor;
                return t && null !== t && (t = e(t).not(this.$slider)), t
            }, t.prototype.asNavFor = function (t) {
                var n = this.getNavTarget();
                null !== n && "object" == typeof n && n.each(function () {
                    var n = e(this).slick("getSlick");
                    n.unslicked || n.slideHandler(t, !0)
                })
            }, t.prototype.applyTransition = function (e) {
                var t = this, n = {};
                !1 === t.options.fade ? n[t.transitionType] = t.transformType + " " + t.options.speed + "ms " + t.options.cssEase : n[t.transitionType] = "opacity " + t.options.speed + "ms " + t.options.cssEase, !1 === t.options.fade ? t.$slideTrack.css(n) : t.$slides.eq(e).css(n)
            }, t.prototype.autoPlay = function () {
                var e = this;
                e.autoPlayClear(), e.slideCount > e.options.slidesToShow && (e.autoPlayTimer = setInterval(e.autoPlayIterator, e.options.autoplaySpeed))
            }, t.prototype.autoPlayClear = function () {
                this.autoPlayTimer && clearInterval(this.autoPlayTimer)
            }, t.prototype.autoPlayIterator = function () {
                var e = this, t = e.currentSlide + e.options.slidesToScroll;
                e.paused || e.interrupted || e.focussed || (!1 === e.options.infinite && (1 === e.direction && e.currentSlide + 1 === e.slideCount - 1 ? e.direction = 0 : 0 === e.direction && (t = e.currentSlide - e.options.slidesToScroll, e.currentSlide - 1 == 0 && (e.direction = 1))), e.slideHandler(t))
            }, t.prototype.buildArrows = function () {
                var t = this;
                !0 === t.options.arrows && (t.$prevArrow = e(t.options.prevArrow).addClass("slick-arrow"), t.$nextArrow = e(t.options.nextArrow).addClass("slick-arrow"), t.slideCount > t.options.slidesToShow ? (t.$prevArrow.removeClass("slick-hidden").removeAttr("aria-hidden tabindex"), t.$nextArrow.removeClass("slick-hidden").removeAttr("aria-hidden tabindex"), t.htmlExpr.test(t.options.prevArrow) && t.$prevArrow.prependTo(t.options.appendArrows), t.htmlExpr.test(t.options.nextArrow) && t.$nextArrow.appendTo(t.options.appendArrows), !0 !== t.options.infinite && t.$prevArrow.addClass("slick-disabled").attr("aria-disabled", "true")) : t.$prevArrow.add(t.$nextArrow).addClass("slick-hidden").attr({
                    "aria-disabled": "true",
                    tabindex: "-1"
                }))
            }, t.prototype.buildDots = function () {
                var t, n, i = this;
                if (!0 === i.options.dots && i.slideCount > i.options.slidesToShow) {
                    for (i.$slider.addClass("slick-dotted"), n = e("<ul />").addClass(i.options.dotsClass), t = 0; t <= i.getDotCount(); t += 1) n.append(e("<li />").append(i.options.customPaging.call(this, i, t)));
                    i.$dots = n.appendTo(i.options.appendDots), i.$dots.find("li").first().addClass("slick-active")
                }
            }, t.prototype.buildOut = function () {
                var t = this;
                t.$slides = t.$slider.children(t.options.slide + ":not(.slick-cloned)").addClass("slick-slide"), t.slideCount = t.$slides.length, t.$slides.each(function (t, n) {
                    e(n).attr("data-slick-index", t).data("originalStyling", e(n).attr("style") || "")
                }), t.$slider.addClass("slick-slider"), t.$slideTrack = 0 === t.slideCount ? e('<div class="slick-track"/>').appendTo(t.$slider) : t.$slides.wrapAll('<div class="slick-track"/>').parent(), t.$list = t.$slideTrack.wrap('<div class="slick-list"/>').parent(), t.$slideTrack.css("opacity", 0), !0 !== t.options.centerMode && !0 !== t.options.swipeToSlide || (t.options.slidesToScroll = 1), e("img[data-lazy]", t.$slider).not("[src]").addClass("slick-loading"), t.setupInfinite(), t.buildArrows(), t.buildDots(), t.updateDots(), t.setSlideClasses("number" == typeof t.currentSlide ? t.currentSlide : 0), !0 === t.options.draggable && t.$list.addClass("draggable")
            }, t.prototype.buildRows = function () {
                var e, t, n, i, o, r, s, a = this;
                if (i = document.createDocumentFragment(), r = a.$slider.children(), a.options.rows > 0) {
                    for (s = a.options.slidesPerRow * a.options.rows, o = Math.ceil(r.length / s), e = 0; e < o; e++) {
                        var l = document.createElement("div");
                        for (t = 0; t < a.options.rows; t++) {
                            var c = document.createElement("div");
                            for (n = 0; n < a.options.slidesPerRow; n++) {
                                var u = e * s + (t * a.options.slidesPerRow + n);
                                r.get(u) && c.appendChild(r.get(u))
                            }
                            l.appendChild(c)
                        }
                        i.appendChild(l)
                    }
                    a.$slider.empty().append(i), a.$slider.children().children().children().css({
                        width: 100 / a.options.slidesPerRow + "%",
                        display: "inline-block"
                    })
                }
            }, t.prototype.checkResponsive = function (t, n) {
                var i, o, r, s = this, a = !1, l = s.$slider.width(), c = window.innerWidth || e(window).width();
                if ("window" === s.respondTo ? r = c : "slider" === s.respondTo ? r = l : "min" === s.respondTo && (r = Math.min(c, l)), s.options.responsive && s.options.responsive.length && null !== s.options.responsive) {
                    for (i in o = null, s.breakpoints) s.breakpoints.hasOwnProperty(i) && (!1 === s.originalSettings.mobileFirst ? r < s.breakpoints[i] && (o = s.breakpoints[i]) : r > s.breakpoints[i] && (o = s.breakpoints[i]));
                    null !== o ? null !== s.activeBreakpoint ? (o !== s.activeBreakpoint || n) && (s.activeBreakpoint = o, "unslick" === s.breakpointSettings[o] ? s.unslick(o) : (s.options = e.extend({}, s.originalSettings, s.breakpointSettings[o]), !0 === t && (s.currentSlide = s.options.initialSlide), s.refresh(t)), a = o) : (s.activeBreakpoint = o, "unslick" === s.breakpointSettings[o] ? s.unslick(o) : (s.options = e.extend({}, s.originalSettings, s.breakpointSettings[o]), !0 === t && (s.currentSlide = s.options.initialSlide), s.refresh(t)), a = o) : null !== s.activeBreakpoint && (s.activeBreakpoint = null, s.options = s.originalSettings, !0 === t && (s.currentSlide = s.options.initialSlide), s.refresh(t), a = o), t || !1 === a || s.$slider.trigger("breakpoint", [s, a])
                }
            }, t.prototype.changeSlide = function (t, n) {
                var i, o, r, s = this, a = e(t.currentTarget);
                switch (a.is("a") && t.preventDefault(), a.is("li") || (a = a.closest("li")), r = s.slideCount % s.options.slidesToScroll != 0, i = r ? 0 : (s.slideCount - s.currentSlide) % s.options.slidesToScroll, t.data.message) {
                    case"previous":
                        o = 0 === i ? s.options.slidesToScroll : s.options.slidesToShow - i, s.slideCount > s.options.slidesToShow && s.slideHandler(s.currentSlide - o, !1, n);
                        break;
                    case"next":
                        o = 0 === i ? s.options.slidesToScroll : i, s.slideCount > s.options.slidesToShow && s.slideHandler(s.currentSlide + o, !1, n);
                        break;
                    case"index":
                        var l = 0 === t.data.index ? 0 : t.data.index || a.index() * s.options.slidesToScroll;
                        s.slideHandler(s.checkNavigable(l), !1, n), a.children().trigger("focus");
                        break;
                    default:
                        return
                }
            }, t.prototype.checkNavigable = function (e) {
                var t, n;
                if (t = this.getNavigableIndexes(), n = 0, e > t[t.length - 1]) e = t[t.length - 1]; else for (var i in t) {
                    if (e < t[i]) {
                        e = n;
                        break
                    }
                    n = t[i]
                }
                return e
            }, t.prototype.cleanUpEvents = function () {
                var t = this;
                t.options.dots && null !== t.$dots && (e("li", t.$dots).off("click.slick", t.changeSlide).off("mouseenter.slick", e.proxy(t.interrupt, t, !0)).off("mouseleave.slick", e.proxy(t.interrupt, t, !1)), !0 === t.options.accessibility && t.$dots.off("keydown.slick", t.keyHandler)), t.$slider.off("focus.slick blur.slick"), !0 === t.options.arrows && t.slideCount > t.options.slidesToShow && (t.$prevArrow && t.$prevArrow.off("click.slick", t.changeSlide), t.$nextArrow && t.$nextArrow.off("click.slick", t.changeSlide), !0 === t.options.accessibility && (t.$prevArrow && t.$prevArrow.off("keydown.slick", t.keyHandler), t.$nextArrow && t.$nextArrow.off("keydown.slick", t.keyHandler))), t.$list.off("touchstart.slick mousedown.slick", t.swipeHandler), t.$list.off("touchmove.slick mousemove.slick", t.swipeHandler), t.$list.off("touchend.slick mouseup.slick", t.swipeHandler), t.$list.off("touchcancel.slick mouseleave.slick", t.swipeHandler), t.$list.off("click.slick", t.clickHandler), e(document).off(t.visibilityChange, t.visibility), t.cleanUpSlideEvents(), !0 === t.options.accessibility && t.$list.off("keydown.slick", t.keyHandler), !0 === t.options.focusOnSelect && e(t.$slideTrack).children().off("click.slick", t.selectHandler), e(window).off("orientationchange.slick.slick-" + t.instanceUid, t.orientationChange), e(window).off("resize.slick.slick-" + t.instanceUid, t.resize), e("[draggable!=true]", t.$slideTrack).off("dragstart", t.preventDefault), e(window).off("load.slick.slick-" + t.instanceUid, t.setPosition)
            }, t.prototype.cleanUpSlideEvents = function () {
                var t = this;
                t.$list.off("mouseenter.slick", e.proxy(t.interrupt, t, !0)), t.$list.off("mouseleave.slick", e.proxy(t.interrupt, t, !1))
            }, t.prototype.cleanUpRows = function () {
                var e, t = this;
                t.options.rows > 0 && ((e = t.$slides.children().children()).removeAttr("style"), t.$slider.empty().append(e))
            }, t.prototype.clickHandler = function (e) {
                !1 === this.shouldClick && (e.stopImmediatePropagation(), e.stopPropagation(), e.preventDefault())
            }, t.prototype.destroy = function (t) {
                var n = this;
                n.autoPlayClear(), n.touchObject = {}, n.cleanUpEvents(), e(".slick-cloned", n.$slider).detach(), n.$dots && n.$dots.remove(), n.$prevArrow && n.$prevArrow.length && (n.$prevArrow.removeClass("slick-disabled slick-arrow slick-hidden").removeAttr("aria-hidden aria-disabled tabindex").css("display", ""), n.htmlExpr.test(n.options.prevArrow) && n.$prevArrow.remove()), n.$nextArrow && n.$nextArrow.length && (n.$nextArrow.removeClass("slick-disabled slick-arrow slick-hidden").removeAttr("aria-hidden aria-disabled tabindex").css("display", ""), n.htmlExpr.test(n.options.nextArrow) && n.$nextArrow.remove()), n.$slides && (n.$slides.removeClass("slick-slide slick-active slick-center slick-visible slick-current").removeAttr("aria-hidden").removeAttr("data-slick-index").each(function () {
                    e(this).attr("style", e(this).data("originalStyling"))
                }), n.$slideTrack.children(this.options.slide).detach(), n.$slideTrack.detach(), n.$list.detach(), n.$slider.append(n.$slides)), n.cleanUpRows(), n.$slider.removeClass("slick-slider"), n.$slider.removeClass("slick-initialized"), n.$slider.removeClass("slick-dotted"), n.unslicked = !0, t || n.$slider.trigger("destroy", [n])
            }, t.prototype.disableTransition = function (e) {
                var t = this, n = {};
                n[t.transitionType] = "", !1 === t.options.fade ? t.$slideTrack.css(n) : t.$slides.eq(e).css(n)
            }, t.prototype.fadeSlide = function (e, t) {
                var n = this;
                !1 === n.cssTransitions ? (n.$slides.eq(e).css({zIndex: n.options.zIndex}), n.$slides.eq(e).animate({opacity: 1}, n.options.speed, n.options.easing, t)) : (n.applyTransition(e), n.$slides.eq(e).css({
                    opacity: 1,
                    zIndex: n.options.zIndex
                }), t && setTimeout(function () {
                    n.disableTransition(e), t.call()
                }, n.options.speed))
            }, t.prototype.fadeSlideOut = function (e) {
                var t = this;
                !1 === t.cssTransitions ? t.$slides.eq(e).animate({
                    opacity: 0,
                    zIndex: t.options.zIndex - 2
                }, t.options.speed, t.options.easing) : (t.applyTransition(e), t.$slides.eq(e).css({
                    opacity: 0,
                    zIndex: t.options.zIndex - 2
                }))
            }, t.prototype.filterSlides = t.prototype.slickFilter = function (e) {
                var t = this;
                null !== e && (t.$slidesCache = t.$slides, t.unload(), t.$slideTrack.children(this.options.slide).detach(), t.$slidesCache.filter(e).appendTo(t.$slideTrack), t.reinit())
            }, t.prototype.focusHandler = function () {
                var t = this;
                t.$slider.off("focus.slick blur.slick").on("focus.slick blur.slick", "*", function (n) {
                    n.stopImmediatePropagation();
                    var i = e(this);
                    setTimeout(function () {
                        t.options.pauseOnFocus && (t.focussed = i.is(":focus"), t.autoPlay())
                    }, 0)
                })
            }, t.prototype.getCurrent = t.prototype.slickCurrentSlide = function () {
                return this.currentSlide
            }, t.prototype.getDotCount = function () {
                var e = this, t = 0, n = 0, i = 0;
                if (!0 === e.options.infinite) if (e.slideCount <= e.options.slidesToShow) ++i; else for (; t < e.slideCount;) ++i, t = n + e.options.slidesToScroll, n += e.options.slidesToScroll <= e.options.slidesToShow ? e.options.slidesToScroll : e.options.slidesToShow; else if (!0 === e.options.centerMode) i = e.slideCount; else if (e.options.asNavFor) for (; t < e.slideCount;) ++i, t = n + e.options.slidesToScroll, n += e.options.slidesToScroll <= e.options.slidesToShow ? e.options.slidesToScroll : e.options.slidesToShow; else i = 1 + Math.ceil((e.slideCount - e.options.slidesToShow) / e.options.slidesToScroll);
                return i - 1
            }, t.prototype.getLeft = function (e) {
                var t, n, i, o, r = this, s = 0;
                return r.slideOffset = 0, n = r.$slides.first().outerHeight(!0), !0 === r.options.infinite ? (r.slideCount > r.options.slidesToShow && (r.slideOffset = r.slideWidth * r.options.slidesToShow * -1, o = -1, !0 === r.options.vertical && !0 === r.options.centerMode && (2 === r.options.slidesToShow ? o = -1.5 : 1 === r.options.slidesToShow && (o = -2)), s = n * r.options.slidesToShow * o), r.slideCount % r.options.slidesToScroll != 0 && e + r.options.slidesToScroll > r.slideCount && r.slideCount > r.options.slidesToShow && (e > r.slideCount ? (r.slideOffset = (r.options.slidesToShow - (e - r.slideCount)) * r.slideWidth * -1, s = (r.options.slidesToShow - (e - r.slideCount)) * n * -1) : (r.slideOffset = r.slideCount % r.options.slidesToScroll * r.slideWidth * -1, s = r.slideCount % r.options.slidesToScroll * n * -1))) : e + r.options.slidesToShow > r.slideCount && (r.slideOffset = (e + r.options.slidesToShow - r.slideCount) * r.slideWidth, s = (e + r.options.slidesToShow - r.slideCount) * n), r.slideCount <= r.options.slidesToShow && (r.slideOffset = 0, s = 0), !0 === r.options.centerMode && r.slideCount <= r.options.slidesToShow ? r.slideOffset = r.slideWidth * Math.floor(r.options.slidesToShow) / 2 - r.slideWidth * r.slideCount / 2 : !0 === r.options.centerMode && !0 === r.options.infinite ? r.slideOffset += r.slideWidth * Math.floor(r.options.slidesToShow / 2) - r.slideWidth : !0 === r.options.centerMode && (r.slideOffset = 0, r.slideOffset += r.slideWidth * Math.floor(r.options.slidesToShow / 2)), t = !1 === r.options.vertical ? e * r.slideWidth * -1 + r.slideOffset : e * n * -1 + s, !0 === r.options.variableWidth && (i = r.slideCount <= r.options.slidesToShow || !1 === r.options.infinite ? r.$slideTrack.children(".slick-slide").eq(e) : r.$slideTrack.children(".slick-slide").eq(e + r.options.slidesToShow), t = !0 === r.options.rtl ? i[0] ? -1 * (r.$slideTrack.width() - i[0].offsetLeft - i.width()) : 0 : i[0] ? -1 * i[0].offsetLeft : 0, !0 === r.options.centerMode && (i = r.slideCount <= r.options.slidesToShow || !1 === r.options.infinite ? r.$slideTrack.children(".slick-slide").eq(e) : r.$slideTrack.children(".slick-slide").eq(e + r.options.slidesToShow + 1), t = !0 === r.options.rtl ? i[0] ? -1 * (r.$slideTrack.width() - i[0].offsetLeft - i.width()) : 0 : i[0] ? -1 * i[0].offsetLeft : 0, t += (r.$list.width() - i.outerWidth()) / 2)), t
            }, t.prototype.getOption = t.prototype.slickGetOption = function (e) {
                return this.options[e]
            }, t.prototype.getNavigableIndexes = function () {
                var e, t = this, n = 0, i = 0, o = [];
                for (!1 === t.options.infinite ? e = t.slideCount : (n = -1 * t.options.slidesToScroll, i = -1 * t.options.slidesToScroll, e = 2 * t.slideCount); n < e;) o.push(n), n = i + t.options.slidesToScroll, i += t.options.slidesToScroll <= t.options.slidesToShow ? t.options.slidesToScroll : t.options.slidesToShow;
                return o
            }, t.prototype.getSlick = function () {
                return this
            }, t.prototype.getSlideCount = function () {
                var t, n, i = this;
                return n = !0 === i.options.centerMode ? i.slideWidth * Math.floor(i.options.slidesToShow / 2) : 0, !0 === i.options.swipeToSlide ? (i.$slideTrack.find(".slick-slide").each(function (o, r) {
                    if (r.offsetLeft - n + e(r).outerWidth() / 2 > -1 * i.swipeLeft) return t = r, !1
                }), Math.abs(e(t).attr("data-slick-index") - i.currentSlide) || 1) : i.options.slidesToScroll
            }, t.prototype.goTo = t.prototype.slickGoTo = function (e, t) {
                this.changeSlide({data: {message: "index", index: parseInt(e)}}, t)
            }, t.prototype.init = function (t) {
                var n = this;
                e(n.$slider).hasClass("slick-initialized") || (e(n.$slider).addClass("slick-initialized"), n.buildRows(), n.buildOut(), n.setProps(), n.startLoad(), n.loadSlider(), n.initializeEvents(), n.updateArrows(), n.updateDots(), n.checkResponsive(!0), n.focusHandler()), t && n.$slider.trigger("init", [n]), !0 === n.options.accessibility && n.initADA(), n.options.autoplay && (n.paused = !1, n.autoPlay())
            }, t.prototype.initADA = function () {
                var t = this, n = Math.ceil(t.slideCount / t.options.slidesToShow),
                    i = t.getNavigableIndexes().filter(function (e) {
                        return e >= 0 && e < t.slideCount
                    });
                t.$slides.add(t.$slideTrack.find(".slick-cloned")).attr({
                    "aria-hidden": "true",
                    tabindex: "-1"
                }).find("a, input, button, select").attr({tabindex: "-1"}), null !== t.$dots && (t.$slides.not(t.$slideTrack.find(".slick-cloned")).each(function (n) {
                    var o = i.indexOf(n);
                    if (e(this).attr({
                        role: "tabpanel",
                        id: "slick-slide" + t.instanceUid + n,
                        tabindex: -1
                    }), -1 !== o) {
                        var r = "slick-slide-control" + t.instanceUid + o;
                        e("#" + r).length && e(this).attr({"aria-describedby": r})
                    }
                }), t.$dots.attr("role", "tablist").find("li").each(function (o) {
                    var r = i[o];
                    e(this).attr({role: "presentation"}), e(this).find("button").first().attr({
                        role: "tab",
                        id: "slick-slide-control" + t.instanceUid + o,
                        "aria-controls": "slick-slide" + t.instanceUid + r,
                        "aria-label": o + 1 + " of " + n,
                        "aria-selected": null,
                        tabindex: "-1"
                    })
                }).eq(t.currentSlide).find("button").attr({"aria-selected": "true", tabindex: "0"}).end());
                for (var o = t.currentSlide, r = o + t.options.slidesToShow; o < r; o++) t.options.focusOnChange ? t.$slides.eq(o).attr({tabindex: "0"}) : t.$slides.eq(o).removeAttr("tabindex");
                t.activateADA()
            }, t.prototype.initArrowEvents = function () {
                var e = this;
                !0 === e.options.arrows && e.slideCount > e.options.slidesToShow && (e.$prevArrow.off("click.slick").on("click.slick", {message: "previous"}, e.changeSlide), e.$nextArrow.off("click.slick").on("click.slick", {message: "next"}, e.changeSlide), !0 === e.options.accessibility && (e.$prevArrow.on("keydown.slick", e.keyHandler), e.$nextArrow.on("keydown.slick", e.keyHandler)))
            }, t.prototype.initDotEvents = function () {
                var t = this;
                !0 === t.options.dots && t.slideCount > t.options.slidesToShow && (e("li", t.$dots).on("click.slick", {message: "index"}, t.changeSlide), !0 === t.options.accessibility && t.$dots.on("keydown.slick", t.keyHandler)), !0 === t.options.dots && !0 === t.options.pauseOnDotsHover && t.slideCount > t.options.slidesToShow && e("li", t.$dots).on("mouseenter.slick", e.proxy(t.interrupt, t, !0)).on("mouseleave.slick", e.proxy(t.interrupt, t, !1))
            }, t.prototype.initSlideEvents = function () {
                var t = this;
                t.options.pauseOnHover && (t.$list.on("mouseenter.slick", e.proxy(t.interrupt, t, !0)), t.$list.on("mouseleave.slick", e.proxy(t.interrupt, t, !1)))
            }, t.prototype.initializeEvents = function () {
                var t = this;
                t.initArrowEvents(), t.initDotEvents(), t.initSlideEvents(), t.$list.on("touchstart.slick mousedown.slick", {action: "start"}, t.swipeHandler), t.$list.on("touchmove.slick mousemove.slick", {action: "move"}, t.swipeHandler), t.$list.on("touchend.slick mouseup.slick", {action: "end"}, t.swipeHandler), t.$list.on("touchcancel.slick mouseleave.slick", {action: "end"}, t.swipeHandler), t.$list.on("click.slick", t.clickHandler), e(document).on(t.visibilityChange, e.proxy(t.visibility, t)), !0 === t.options.accessibility && t.$list.on("keydown.slick", t.keyHandler), !0 === t.options.focusOnSelect && e(t.$slideTrack).children().on("click.slick", t.selectHandler), e(window).on("orientationchange.slick.slick-" + t.instanceUid, e.proxy(t.orientationChange, t)), e(window).on("resize.slick.slick-" + t.instanceUid, e.proxy(t.resize, t)), e("[draggable!=true]", t.$slideTrack).on("dragstart", t.preventDefault), e(window).on("load.slick.slick-" + t.instanceUid, t.setPosition), e(t.setPosition)
            }, t.prototype.initUI = function () {
                var e = this;
                !0 === e.options.arrows && e.slideCount > e.options.slidesToShow && (e.$prevArrow.show(), e.$nextArrow.show()), !0 === e.options.dots && e.slideCount > e.options.slidesToShow && e.$dots.show()
            }, t.prototype.keyHandler = function (e) {
                var t = this;
                e.target.tagName.match("TEXTAREA|INPUT|SELECT") || (37 === e.keyCode && !0 === t.options.accessibility ? t.changeSlide({data: {message: !0 === t.options.rtl ? "next" : "previous"}}) : 39 === e.keyCode && !0 === t.options.accessibility && t.changeSlide({data: {message: !0 === t.options.rtl ? "previous" : "next"}}))
            }, t.prototype.lazyLoad = function () {
                var t, n, i, o = this;

                function r(t) {
                    e("img[data-lazy]", t).each(function () {
                        var t = e(this), n = e(this).attr("data-lazy"), i = e(this).attr("data-srcset"),
                            r = e(this).attr("data-sizes") || o.$slider.attr("data-sizes"),
                            s = document.createElement("img");
                        s.onload = function () {
                            t.animate({opacity: 0}, 100, function () {
                                i && (t.attr("srcset", i), r && t.attr("sizes", r)), t.attr("src", n).animate({opacity: 1}, 200, function () {
                                    t.removeAttr("data-lazy data-srcset data-sizes").removeClass("slick-loading")
                                }), o.$slider.trigger("lazyLoaded", [o, t, n])
                            })
                        }, s.onerror = function () {
                            t.removeAttr("data-lazy").removeClass("slick-loading").addClass("slick-lazyload-error"), o.$slider.trigger("lazyLoadError", [o, t, n])
                        }, s.src = n
                    })
                }

                if (!0 === o.options.centerMode ? !0 === o.options.infinite ? (n = o.currentSlide + (o.options.slidesToShow / 2 + 1), i = n + o.options.slidesToShow + 2) : (n = Math.max(0, o.currentSlide - (o.options.slidesToShow / 2 + 1)), i = o.options.slidesToShow / 2 + 1 + 2 + o.currentSlide) : (n = o.options.infinite ? o.options.slidesToShow + o.currentSlide : o.currentSlide, i = Math.ceil(n + o.options.slidesToShow), !0 === o.options.fade && (n > 0 && n--, i <= o.slideCount && i++)), t = o.$slider.find(".slick-slide").slice(n, i), "anticipated" === o.options.lazyLoad) for (var s = n - 1, a = i, l = o.$slider.find(".slick-slide"), c = 0; c < o.options.slidesToScroll; c++) s < 0 && (s = o.slideCount - 1), t = (t = t.add(l.eq(s))).add(l.eq(a)), s--, a++;
                r(t), o.slideCount <= o.options.slidesToShow ? r(o.$slider.find(".slick-slide")) : o.currentSlide >= o.slideCount - o.options.slidesToShow ? r(o.$slider.find(".slick-cloned").slice(0, o.options.slidesToShow)) : 0 === o.currentSlide && r(o.$slider.find(".slick-cloned").slice(-1 * o.options.slidesToShow))
            }, t.prototype.loadSlider = function () {
                var e = this;
                e.setPosition(), e.$slideTrack.css({opacity: 1}), e.$slider.removeClass("slick-loading"), e.initUI(), "progressive" === e.options.lazyLoad && e.progressiveLazyLoad()
            }, t.prototype.next = t.prototype.slickNext = function () {
                this.changeSlide({data: {message: "next"}})
            }, t.prototype.orientationChange = function () {
                this.checkResponsive(), this.setPosition()
            }, t.prototype.pause = t.prototype.slickPause = function () {
                this.autoPlayClear(), this.paused = !0
            }, t.prototype.play = t.prototype.slickPlay = function () {
                var e = this;
                e.autoPlay(), e.options.autoplay = !0, e.paused = !1, e.focussed = !1, e.interrupted = !1
            }, t.prototype.postSlide = function (t) {
                var n = this;
                if (!n.unslicked && (n.$slider.trigger("afterChange", [n, t]), n.animating = !1, n.slideCount > n.options.slidesToShow && n.setPosition(), n.swipeLeft = null, n.options.autoplay && n.autoPlay(), !0 === n.options.accessibility && (n.initADA(), n.options.focusOnChange))) {
                    var i = e(n.$slides.get(n.currentSlide));
                    i.attr("tabindex", 0).focus()
                }
            }, t.prototype.prev = t.prototype.slickPrev = function () {
                this.changeSlide({data: {message: "previous"}})
            }, t.prototype.preventDefault = function (e) {
                e.preventDefault()
            }, t.prototype.progressiveLazyLoad = function (t) {
                t = t || 1;
                var n, i, o, r, s, a = this, l = e("img[data-lazy]", a.$slider);
                l.length ? (n = l.first(), i = n.attr("data-lazy"), o = n.attr("data-srcset"), r = n.attr("data-sizes") || a.$slider.attr("data-sizes"), (s = document.createElement("img")).onload = function () {
                    o && (n.attr("srcset", o), r && n.attr("sizes", r)), n.attr("src", i).removeAttr("data-lazy data-srcset data-sizes").removeClass("slick-loading"), !0 === a.options.adaptiveHeight && a.setPosition(), a.$slider.trigger("lazyLoaded", [a, n, i]), a.progressiveLazyLoad()
                }, s.onerror = function () {
                    t < 3 ? setTimeout(function () {
                        a.progressiveLazyLoad(t + 1)
                    }, 500) : (n.removeAttr("data-lazy").removeClass("slick-loading").addClass("slick-lazyload-error"), a.$slider.trigger("lazyLoadError", [a, n, i]), a.progressiveLazyLoad())
                }, s.src = i) : a.$slider.trigger("allImagesLoaded", [a])
            }, t.prototype.refresh = function (t) {
                var n, i, o = this;
                i = o.slideCount - o.options.slidesToShow, !o.options.infinite && o.currentSlide > i && (o.currentSlide = i), o.slideCount <= o.options.slidesToShow && (o.currentSlide = 0), n = o.currentSlide, o.destroy(!0), e.extend(o, o.initials, {currentSlide: n}), o.init(), t || o.changeSlide({
                    data: {
                        message: "index",
                        index: n
                    }
                }, !1)
            }, t.prototype.registerBreakpoints = function () {
                var t, n, i, o = this, r = o.options.responsive || null;
                if ("array" === e.type(r) && r.length) {
                    for (t in o.respondTo = o.options.respondTo || "window", r) if (i = o.breakpoints.length - 1, r.hasOwnProperty(t)) {
                        for (n = r[t].breakpoint; i >= 0;) o.breakpoints[i] && o.breakpoints[i] === n && o.breakpoints.splice(i, 1), i--;
                        o.breakpoints.push(n), o.breakpointSettings[n] = r[t].settings
                    }
                    o.breakpoints.sort(function (e, t) {
                        return o.options.mobileFirst ? e - t : t - e
                    })
                }
            }, t.prototype.reinit = function () {
                var t = this;
                t.$slides = t.$slideTrack.children(t.options.slide).addClass("slick-slide"), t.slideCount = t.$slides.length, t.currentSlide >= t.slideCount && 0 !== t.currentSlide && (t.currentSlide = t.currentSlide - t.options.slidesToScroll), t.slideCount <= t.options.slidesToShow && (t.currentSlide = 0), t.registerBreakpoints(), t.setProps(), t.setupInfinite(), t.buildArrows(), t.updateArrows(), t.initArrowEvents(), t.buildDots(), t.updateDots(), t.initDotEvents(), t.cleanUpSlideEvents(), t.initSlideEvents(), t.checkResponsive(!1, !0), !0 === t.options.focusOnSelect && e(t.$slideTrack).children().on("click.slick", t.selectHandler), t.setSlideClasses("number" == typeof t.currentSlide ? t.currentSlide : 0), t.setPosition(), t.focusHandler(), t.paused = !t.options.autoplay, t.autoPlay(), t.$slider.trigger("reInit", [t])
            }, t.prototype.resize = function () {
                var t = this;
                e(window).width() !== t.windowWidth && (clearTimeout(t.windowDelay), t.windowDelay = window.setTimeout(function () {
                    t.windowWidth = e(window).width(), t.checkResponsive(), t.unslicked || t.setPosition()
                }, 50))
            }, t.prototype.removeSlide = t.prototype.slickRemove = function (e, t, n) {
                var i = this;
                if (e = "boolean" == typeof e ? !0 === (t = e) ? 0 : i.slideCount - 1 : !0 === t ? --e : e, i.slideCount < 1 || e < 0 || e > i.slideCount - 1) return !1;
                i.unload(), !0 === n ? i.$slideTrack.children().remove() : i.$slideTrack.children(this.options.slide).eq(e).remove(), i.$slides = i.$slideTrack.children(this.options.slide), i.$slideTrack.children(this.options.slide).detach(), i.$slideTrack.append(i.$slides), i.$slidesCache = i.$slides, i.reinit()
            }, t.prototype.setCSS = function (e) {
                var t, n, i = this, o = {};
                !0 === i.options.rtl && (e = -e), t = "left" == i.positionProp ? Math.ceil(e) + "px" : "0px", n = "top" == i.positionProp ? Math.ceil(e) + "px" : "0px", o[i.positionProp] = e, !1 === i.transformsEnabled ? i.$slideTrack.css(o) : (o = {}, !1 === i.cssTransitions ? (o[i.animType] = "translate(" + t + ", " + n + ")", i.$slideTrack.css(o)) : (o[i.animType] = "translate3d(" + t + ", " + n + ", 0px)", i.$slideTrack.css(o)))
            }, t.prototype.setDimensions = function () {
                var e = this;
                !1 === e.options.vertical ? !0 === e.options.centerMode && e.$list.css({padding: "0px " + e.options.centerPadding}) : (e.$list.height(e.$slides.first().outerHeight(!0) * e.options.slidesToShow), !0 === e.options.centerMode && e.$list.css({padding: e.options.centerPadding + " 0px"})), e.listWidth = e.$list.width(), e.listHeight = e.$list.height(), !1 === e.options.vertical && !1 === e.options.variableWidth ? (e.slideWidth = Math.ceil(e.listWidth / e.options.slidesToShow), e.$slideTrack.width(Math.ceil(e.slideWidth * e.$slideTrack.children(".slick-slide").length))) : !0 === e.options.variableWidth ? e.$slideTrack.width(5e3 * e.slideCount) : (e.slideWidth = Math.ceil(e.listWidth), e.$slideTrack.height(Math.ceil(e.$slides.first().outerHeight(!0) * e.$slideTrack.children(".slick-slide").length)));
                var t = e.$slides.first().outerWidth(!0) - e.$slides.first().width();
                !1 === e.options.variableWidth && e.$slideTrack.children(".slick-slide").width(e.slideWidth - t)
            }, t.prototype.setFade = function () {
                var t, n = this;
                n.$slides.each(function (i, o) {
                    t = n.slideWidth * i * -1, !0 === n.options.rtl ? e(o).css({
                        position: "relative",
                        right: t,
                        top: 0,
                        zIndex: n.options.zIndex - 2,
                        opacity: 0
                    }) : e(o).css({position: "relative", left: t, top: 0, zIndex: n.options.zIndex - 2, opacity: 0})
                }), n.$slides.eq(n.currentSlide).css({zIndex: n.options.zIndex - 1, opacity: 1})
            }, t.prototype.setHeight = function () {
                var e = this;
                if (1 === e.options.slidesToShow && !0 === e.options.adaptiveHeight && !1 === e.options.vertical) {
                    var t = e.$slides.eq(e.currentSlide).outerHeight(!0);
                    e.$list.css("height", t)
                }
            }, t.prototype.setOption = t.prototype.slickSetOption = function () {
                var t, n, i, o, r, s = this, a = !1;
                if ("object" === e.type(arguments[0]) ? (i = arguments[0], a = arguments[1], r = "multiple") : "string" === e.type(arguments[0]) && (i = arguments[0], o = arguments[1], a = arguments[2], "responsive" === arguments[0] && "array" === e.type(arguments[1]) ? r = "responsive" : void 0 !== arguments[1] && (r = "single")), "single" === r) s.options[i] = o; else if ("multiple" === r) e.each(i, function (e, t) {
                    s.options[e] = t
                }); else if ("responsive" === r) for (n in o) if ("array" !== e.type(s.options.responsive)) s.options.responsive = [o[n]]; else {
                    for (t = s.options.responsive.length - 1; t >= 0;) s.options.responsive[t].breakpoint === o[n].breakpoint && s.options.responsive.splice(t, 1), t--;
                    s.options.responsive.push(o[n])
                }
                a && (s.unload(), s.reinit())
            }, t.prototype.setPosition = function () {
                var e = this;
                e.setDimensions(), e.setHeight(), !1 === e.options.fade ? e.setCSS(e.getLeft(e.currentSlide)) : e.setFade(), e.$slider.trigger("setPosition", [e])
            }, t.prototype.setProps = function () {
                var e = this, t = document.body.style;
                e.positionProp = !0 === e.options.vertical ? "top" : "left", "top" === e.positionProp ? e.$slider.addClass("slick-vertical") : e.$slider.removeClass("slick-vertical"), void 0 === t.WebkitTransition && void 0 === t.MozTransition && void 0 === t.msTransition || !0 === e.options.useCSS && (e.cssTransitions = !0), e.options.fade && ("number" == typeof e.options.zIndex ? e.options.zIndex < 3 && (e.options.zIndex = 3) : e.options.zIndex = e.defaults.zIndex), void 0 !== t.OTransform && (e.animType = "OTransform", e.transformType = "-o-transform", e.transitionType = "OTransition", void 0 === t.perspectiveProperty && void 0 === t.webkitPerspective && (e.animType = !1)), void 0 !== t.MozTransform && (e.animType = "MozTransform", e.transformType = "-moz-transform", e.transitionType = "MozTransition", void 0 === t.perspectiveProperty && void 0 === t.MozPerspective && (e.animType = !1)), void 0 !== t.webkitTransform && (e.animType = "webkitTransform", e.transformType = "-webkit-transform", e.transitionType = "webkitTransition", void 0 === t.perspectiveProperty && void 0 === t.webkitPerspective && (e.animType = !1)), void 0 !== t.msTransform && (e.animType = "msTransform", e.transformType = "-ms-transform", e.transitionType = "msTransition", void 0 === t.msTransform && (e.animType = !1)), void 0 !== t.transform && !1 !== e.animType && (e.animType = "transform", e.transformType = "transform", e.transitionType = "transition"), e.transformsEnabled = e.options.useTransform && null !== e.animType && !1 !== e.animType
            }, t.prototype.setSlideClasses = function (e) {
                var t, n, i, o, r = this;
                if (n = r.$slider.find(".slick-slide").removeClass("slick-active slick-center slick-current").attr("aria-hidden", "true"), r.$slides.eq(e).addClass("slick-current"), !0 === r.options.centerMode) {
                    var s = r.options.slidesToShow % 2 == 0 ? 1 : 0;
                    t = Math.floor(r.options.slidesToShow / 2), !0 === r.options.infinite && (e >= t && e <= r.slideCount - 1 - t ? r.$slides.slice(e - t + s, e + t + 1).addClass("slick-active").attr("aria-hidden", "false") : (i = r.options.slidesToShow + e, n.slice(i - t + 1 + s, i + t + 2).addClass("slick-active").attr("aria-hidden", "false")), 0 === e ? n.eq(n.length - 1 - r.options.slidesToShow).addClass("slick-center") : e === r.slideCount - 1 && n.eq(r.options.slidesToShow).addClass("slick-center")), r.$slides.eq(e).addClass("slick-center")
                } else e >= 0 && e <= r.slideCount - r.options.slidesToShow ? r.$slides.slice(e, e + r.options.slidesToShow).addClass("slick-active").attr("aria-hidden", "false") : n.length <= r.options.slidesToShow ? n.addClass("slick-active").attr("aria-hidden", "false") : (o = r.slideCount % r.options.slidesToShow, i = !0 === r.options.infinite ? r.options.slidesToShow + e : e, r.options.slidesToShow == r.options.slidesToScroll && r.slideCount - e < r.options.slidesToShow ? n.slice(i - (r.options.slidesToShow - o), i + o).addClass("slick-active").attr("aria-hidden", "false") : n.slice(i, i + r.options.slidesToShow).addClass("slick-active").attr("aria-hidden", "false"));
                "ondemand" !== r.options.lazyLoad && "anticipated" !== r.options.lazyLoad || r.lazyLoad()
            }, t.prototype.setupInfinite = function () {
                var t, n, i, o = this;
                if (!0 === o.options.fade && (o.options.centerMode = !1), !0 === o.options.infinite && !1 === o.options.fade && (n = null, o.slideCount > o.options.slidesToShow)) {
                    for (i = !0 === o.options.centerMode ? o.options.slidesToShow + 1 : o.options.slidesToShow, t = o.slideCount; t > o.slideCount - i; t -= 1) n = t - 1, e(o.$slides[n]).clone(!0).attr("id", "").attr("data-slick-index", n - o.slideCount).prependTo(o.$slideTrack).addClass("slick-cloned");
                    for (t = 0; t < i + o.slideCount; t += 1) n = t, e(o.$slides[n]).clone(!0).attr("id", "").attr("data-slick-index", n + o.slideCount).appendTo(o.$slideTrack).addClass("slick-cloned");
                    o.$slideTrack.find(".slick-cloned").find("[id]").each(function () {
                        e(this).attr("id", "")
                    })
                }
            }, t.prototype.interrupt = function (e) {
                e || this.autoPlay(), this.interrupted = e
            }, t.prototype.selectHandler = function (t) {
                var n = this, i = e(t.target).is(".slick-slide") ? e(t.target) : e(t.target).parents(".slick-slide"),
                    o = parseInt(i.attr("data-slick-index"));
                o || (o = 0), n.slideCount <= n.options.slidesToShow ? n.slideHandler(o, !1, !0) : n.slideHandler(o)
            }, t.prototype.slideHandler = function (e, t, n) {
                var i, o, r, s, a, l = null, c = this;
                if (t = t || !1, !(!0 === c.animating && !0 === c.options.waitForAnimate || !0 === c.options.fade && c.currentSlide === e)) if (!1 === t && c.asNavFor(e), i = e, l = c.getLeft(i), s = c.getLeft(c.currentSlide), c.currentLeft = null === c.swipeLeft ? s : c.swipeLeft, !1 === c.options.infinite && !1 === c.options.centerMode && (e < 0 || e > c.getDotCount() * c.options.slidesToScroll)) !1 === c.options.fade && (i = c.currentSlide, !0 !== n && c.slideCount > c.options.slidesToShow ? c.animateSlide(s, function () {
                    c.postSlide(i)
                }) : c.postSlide(i)); else if (!1 === c.options.infinite && !0 === c.options.centerMode && (e < 0 || e > c.slideCount - c.options.slidesToScroll)) !1 === c.options.fade && (i = c.currentSlide, !0 !== n && c.slideCount > c.options.slidesToShow ? c.animateSlide(s, function () {
                    c.postSlide(i)
                }) : c.postSlide(i)); else {
                    if (c.options.autoplay && clearInterval(c.autoPlayTimer), o = i < 0 ? c.slideCount % c.options.slidesToScroll != 0 ? c.slideCount - c.slideCount % c.options.slidesToScroll : c.slideCount + i : i >= c.slideCount ? c.slideCount % c.options.slidesToScroll != 0 ? 0 : i - c.slideCount : i, c.animating = !0, c.$slider.trigger("beforeChange", [c, c.currentSlide, o]), r = c.currentSlide, c.currentSlide = o, c.setSlideClasses(c.currentSlide), c.options.asNavFor && (a = (a = c.getNavTarget()).slick("getSlick")).slideCount <= a.options.slidesToShow && a.setSlideClasses(c.currentSlide), c.updateDots(), c.updateArrows(), !0 === c.options.fade) return !0 !== n ? (c.fadeSlideOut(r), c.fadeSlide(o, function () {
                        c.postSlide(o)
                    })) : c.postSlide(o), void c.animateHeight();
                    !0 !== n && c.slideCount > c.options.slidesToShow ? c.animateSlide(l, function () {
                        c.postSlide(o)
                    }) : c.postSlide(o)
                }
            }, t.prototype.startLoad = function () {
                var e = this;
                !0 === e.options.arrows && e.slideCount > e.options.slidesToShow && (e.$prevArrow.hide(), e.$nextArrow.hide()), !0 === e.options.dots && e.slideCount > e.options.slidesToShow && e.$dots.hide(), e.$slider.addClass("slick-loading")
            }, t.prototype.swipeDirection = function () {
                var e, t, n, i, o = this;
                return e = o.touchObject.startX - o.touchObject.curX, t = o.touchObject.startY - o.touchObject.curY, n = Math.atan2(t, e), (i = Math.round(180 * n / Math.PI)) < 0 && (i = 360 - Math.abs(i)), i <= 45 && i >= 0 ? !1 === o.options.rtl ? "left" : "right" : i <= 360 && i >= 315 ? !1 === o.options.rtl ? "left" : "right" : i >= 135 && i <= 225 ? !1 === o.options.rtl ? "right" : "left" : !0 === o.options.verticalSwiping ? i >= 35 && i <= 135 ? "down" : "up" : "vertical"
            }, t.prototype.swipeEnd = function (e) {
                var t, n, i = this;
                if (i.dragging = !1, i.swiping = !1, i.scrolling) return i.scrolling = !1, !1;
                if (i.interrupted = !1, i.shouldClick = !(i.touchObject.swipeLength > 10), void 0 === i.touchObject.curX) return !1;
                if (!0 === i.touchObject.edgeHit && i.$slider.trigger("edge", [i, i.swipeDirection()]), i.touchObject.swipeLength >= i.touchObject.minSwipe) {
                    switch (n = i.swipeDirection()) {
                        case"left":
                        case"down":
                            t = i.options.swipeToSlide ? i.checkNavigable(i.currentSlide + i.getSlideCount()) : i.currentSlide + i.getSlideCount(), i.currentDirection = 0;
                            break;
                        case"right":
                        case"up":
                            t = i.options.swipeToSlide ? i.checkNavigable(i.currentSlide - i.getSlideCount()) : i.currentSlide - i.getSlideCount(), i.currentDirection = 1
                    }
                    "vertical" != n && (i.slideHandler(t), i.touchObject = {}, i.$slider.trigger("swipe", [i, n]))
                } else i.touchObject.startX !== i.touchObject.curX && (i.slideHandler(i.currentSlide), i.touchObject = {})
            }, t.prototype.swipeHandler = function (e) {
                var t = this;
                if (!(!1 === t.options.swipe || "ontouchend" in document && !1 === t.options.swipe || !1 === t.options.draggable && -1 !== e.type.indexOf("mouse"))) switch (t.touchObject.fingerCount = e.originalEvent && void 0 !== e.originalEvent.touches ? e.originalEvent.touches.length : 1, t.touchObject.minSwipe = t.listWidth / t.options.touchThreshold, !0 === t.options.verticalSwiping && (t.touchObject.minSwipe = t.listHeight / t.options.touchThreshold), e.data.action) {
                    case"start":
                        t.swipeStart(e);
                        break;
                    case"move":
                        t.swipeMove(e);
                        break;
                    case"end":
                        t.swipeEnd(e)
                }
            }, t.prototype.swipeMove = function (e) {
                var t, n, i, o, r, s, a = this;
                return r = void 0 !== e.originalEvent ? e.originalEvent.touches : null, !(!a.dragging || a.scrolling || r && 1 !== r.length) && (t = a.getLeft(a.currentSlide), a.touchObject.curX = void 0 !== r ? r[0].pageX : e.clientX, a.touchObject.curY = void 0 !== r ? r[0].pageY : e.clientY, a.touchObject.swipeLength = Math.round(Math.sqrt(Math.pow(a.touchObject.curX - a.touchObject.startX, 2))), s = Math.round(Math.sqrt(Math.pow(a.touchObject.curY - a.touchObject.startY, 2))), !a.options.verticalSwiping && !a.swiping && s > 4 ? (a.scrolling = !0, !1) : (!0 === a.options.verticalSwiping && (a.touchObject.swipeLength = s), n = a.swipeDirection(), void 0 !== e.originalEvent && a.touchObject.swipeLength > 4 && (a.swiping = !0, e.preventDefault()), o = (!1 === a.options.rtl ? 1 : -1) * (a.touchObject.curX > a.touchObject.startX ? 1 : -1), !0 === a.options.verticalSwiping && (o = a.touchObject.curY > a.touchObject.startY ? 1 : -1), i = a.touchObject.swipeLength, a.touchObject.edgeHit = !1, !1 === a.options.infinite && (0 === a.currentSlide && "right" === n || a.currentSlide >= a.getDotCount() && "left" === n) && (i = a.touchObject.swipeLength * a.options.edgeFriction, a.touchObject.edgeHit = !0), !1 === a.options.vertical ? a.swipeLeft = t + i * o : a.swipeLeft = t + i * (a.$list.height() / a.listWidth) * o, !0 === a.options.verticalSwiping && (a.swipeLeft = t + i * o), !0 !== a.options.fade && !1 !== a.options.touchMove && (!0 === a.animating ? (a.swipeLeft = null, !1) : void a.setCSS(a.swipeLeft))))
            }, t.prototype.swipeStart = function (e) {
                var t, n = this;
                if (n.interrupted = !0, 1 !== n.touchObject.fingerCount || n.slideCount <= n.options.slidesToShow) return n.touchObject = {}, !1;
                void 0 !== e.originalEvent && void 0 !== e.originalEvent.touches && (t = e.originalEvent.touches[0]), n.touchObject.startX = n.touchObject.curX = void 0 !== t ? t.pageX : e.clientX, n.touchObject.startY = n.touchObject.curY = void 0 !== t ? t.pageY : e.clientY, n.dragging = !0
            }, t.prototype.unfilterSlides = t.prototype.slickUnfilter = function () {
                var e = this;
                null !== e.$slidesCache && (e.unload(), e.$slideTrack.children(this.options.slide).detach(), e.$slidesCache.appendTo(e.$slideTrack), e.reinit())
            }, t.prototype.unload = function () {
                var t = this;
                e(".slick-cloned", t.$slider).remove(), t.$dots && t.$dots.remove(), t.$prevArrow && t.htmlExpr.test(t.options.prevArrow) && t.$prevArrow.remove(), t.$nextArrow && t.htmlExpr.test(t.options.nextArrow) && t.$nextArrow.remove(), t.$slides.removeClass("slick-slide slick-active slick-visible slick-current").attr("aria-hidden", "true").css("width", "")
            }, t.prototype.unslick = function (e) {
                var t = this;
                t.$slider.trigger("unslick", [t, e]), t.destroy()
            }, t.prototype.updateArrows = function () {
                var e = this;
                Math.floor(e.options.slidesToShow / 2), !0 === e.options.arrows && e.slideCount > e.options.slidesToShow && !e.options.infinite && (e.$prevArrow.removeClass("slick-disabled").attr("aria-disabled", "false"), e.$nextArrow.removeClass("slick-disabled").attr("aria-disabled", "false"), 0 === e.currentSlide ? (e.$prevArrow.addClass("slick-disabled").attr("aria-disabled", "true"), e.$nextArrow.removeClass("slick-disabled").attr("aria-disabled", "false")) : e.currentSlide >= e.slideCount - e.options.slidesToShow && !1 === e.options.centerMode ? (e.$nextArrow.addClass("slick-disabled").attr("aria-disabled", "true"), e.$prevArrow.removeClass("slick-disabled").attr("aria-disabled", "false")) : e.currentSlide >= e.slideCount - 1 && !0 === e.options.centerMode && (e.$nextArrow.addClass("slick-disabled").attr("aria-disabled", "true"), e.$prevArrow.removeClass("slick-disabled").attr("aria-disabled", "false")))
            }, t.prototype.updateDots = function () {
                var e = this;
                null !== e.$dots && (e.$dots.find("li").removeClass("slick-active").end(), e.$dots.find("li").eq(Math.floor(e.currentSlide / e.options.slidesToScroll)).addClass("slick-active"))
            }, t.prototype.visibility = function () {
                var e = this;
                e.options.autoplay && (document[e.hidden] ? e.interrupted = !0 : e.interrupted = !1)
            }, e.fn.slick = function () {
                var e, n, i = this, o = arguments[0], r = Array.prototype.slice.call(arguments, 1), s = i.length;
                for (e = 0; e < s; e++) if ("object" == typeof o || void 0 === o ? i[e].slick = new t(i[e], o) : n = i[e].slick[o].apply(i[e].slick, r), void 0 !== n) return n;
                return i
            }
        }) ? i.apply(t, o) : i) || (e.exports = r)
    }()
}, function (e, t, n) {
    window.Typed = n(62), function (e) {
        page.registerVendor("Typed"), page.initTyped = function () {
            e("[data-typing]").each(function () {
                var t = {
                    strings: e(this).data("typing").split(","),
                    typeSpeed: 50,
                    backSpeed: 30,
                    backDelay: 800,
                    loop: !0
                };
                t = e.extend(t, page.getDataOptions(e(this)));
                new Typed(e(this)[0], t)
            })
        }
    }(jQuery)
}, function (e, t, n) {
    /*!
 *
 *   typed.js - A JavaScript Typing Animation Library
 *   Author: Matt Boldt <me@mattboldt.com>
 *   Version: v2.0.8
 *   Url: https://github.com/mattboldt/typed.js
 *   License(s): MIT
 *
 */
    !function (t, n) {
        e.exports = n()
    }(0, function () {
        return function (e) {
            var t = {};

            function n(i) {
                if (t[i]) return t[i].exports;
                var o = t[i] = {exports: {}, id: i, loaded: !1};
                return e[i].call(o.exports, o, o.exports, n), o.loaded = !0, o.exports
            }

            return n.m = e, n.c = t, n.p = "", n(0)
        }([function (e, t, n) {
            "use strict";
            Object.defineProperty(t, "__esModule", {value: !0});
            var i = function () {
                function e(e, t) {
                    for (var n = 0; n < t.length; n++) {
                        var i = t[n];
                        i.enumerable = i.enumerable || !1, i.configurable = !0, "value" in i && (i.writable = !0), Object.defineProperty(e, i.key, i)
                    }
                }

                return function (t, n, i) {
                    return n && e(t.prototype, n), i && e(t, i), t
                }
            }();
            var o = n(1), r = n(3), s = function () {
                function e(t, n) {
                    !function (e, t) {
                        if (!(e instanceof t)) throw new TypeError("Cannot call a class as a function")
                    }(this, e), o.initializer.load(this, n, t), this.begin()
                }

                return i(e, [{
                    key: "toggle", value: function () {
                        this.pause.status ? this.start() : this.stop()
                    }
                }, {
                    key: "stop", value: function () {
                        this.typingComplete || this.pause.status || (this.toggleBlinking(!0), this.pause.status = !0, this.options.onStop(this.arrayPos, this))
                    }
                }, {
                    key: "start", value: function () {
                        this.typingComplete || this.pause.status && (this.pause.status = !1, this.pause.typewrite ? this.typewrite(this.pause.curString, this.pause.curStrPos) : this.backspace(this.pause.curString, this.pause.curStrPos), this.options.onStart(this.arrayPos, this))
                    }
                }, {
                    key: "destroy", value: function () {
                        this.reset(!1), this.options.onDestroy(this)
                    }
                }, {
                    key: "reset", value: function () {
                        var e = arguments.length <= 0 || void 0 === arguments[0] || arguments[0];
                        clearInterval(this.timeout), this.replaceText(""), this.cursor && this.cursor.parentNode && (this.cursor.parentNode.removeChild(this.cursor), this.cursor = null), this.strPos = 0, this.arrayPos = 0, this.curLoop = 0, e && (this.insertCursor(), this.options.onReset(this), this.begin())
                    }
                }, {
                    key: "begin", value: function () {
                        var e = this;
                        this.typingComplete = !1, this.shuffleStringsIfNeeded(this), this.insertCursor(), this.bindInputFocusEvents && this.bindFocusEvents(), this.timeout = setTimeout(function () {
                            e.currentElContent && 0 !== e.currentElContent.length ? e.backspace(e.currentElContent, e.currentElContent.length) : e.typewrite(e.strings[e.sequence[e.arrayPos]], e.strPos)
                        }, this.startDelay)
                    }
                }, {
                    key: "typewrite", value: function (e, t) {
                        var n = this;
                        this.fadeOut && this.el.classList.contains(this.fadeOutClass) && (this.el.classList.remove(this.fadeOutClass), this.cursor && this.cursor.classList.remove(this.fadeOutClass));
                        var i = this.humanizer(this.typeSpeed), o = 1;
                        !0 !== this.pause.status ? this.timeout = setTimeout(function () {
                            t = r.htmlParser.typeHtmlChars(e, t, n);
                            var i = 0, s = e.substr(t);
                            if ("^" === s.charAt(0) && /^\^\d+/.test(s)) {
                                var a = 1;
                                a += (s = /\d+/.exec(s)[0]).length, i = parseInt(s), n.temporaryPause = !0, n.options.onTypingPaused(n.arrayPos, n), e = e.substring(0, t) + e.substring(t + a), n.toggleBlinking(!0)
                            }
                            if ("`" === s.charAt(0)) {
                                for (; "`" !== e.substr(t + o).charAt(0) && !(t + ++o > e.length);) ;
                                var l = e.substring(0, t), c = e.substring(l.length + 1, t + o),
                                    u = e.substring(t + o + 1);
                                e = l + c + u, o--
                            }
                            n.timeout = setTimeout(function () {
                                n.toggleBlinking(!1), t === e.length ? n.doneTyping(e, t) : n.keepTyping(e, t, o), n.temporaryPause && (n.temporaryPause = !1, n.options.onTypingResumed(n.arrayPos, n))
                            }, i)
                        }, i) : this.setPauseStatus(e, t, !0)
                    }
                }, {
                    key: "keepTyping", value: function (e, t, n) {
                        0 === t && (this.toggleBlinking(!1), this.options.preStringTyped(this.arrayPos, this)), t += n;
                        var i = e.substr(0, t);
                        this.replaceText(i), this.typewrite(e, t)
                    }
                }, {
                    key: "doneTyping", value: function (e, t) {
                        var n = this;
                        this.options.onStringTyped(this.arrayPos, this), this.toggleBlinking(!0), this.arrayPos === this.strings.length - 1 && (this.complete(), !1 === this.loop || this.curLoop === this.loopCount) || (this.timeout = setTimeout(function () {
                            n.backspace(e, t)
                        }, this.backDelay))
                    }
                }, {
                    key: "backspace", value: function (e, t) {
                        var n = this;
                        if (!0 !== this.pause.status) {
                            if (this.fadeOut) return this.initFadeOut();
                            this.toggleBlinking(!1);
                            var i = this.humanizer(this.backSpeed);
                            this.timeout = setTimeout(function () {
                                t = r.htmlParser.backSpaceHtmlChars(e, t, n);
                                var i = e.substr(0, t);
                                if (n.replaceText(i), n.smartBackspace) {
                                    var o = n.strings[n.arrayPos + 1];
                                    o && i === o.substr(0, t) ? n.stopNum = t : n.stopNum = 0
                                }
                                t > n.stopNum ? (t--, n.backspace(e, t)) : t <= n.stopNum && (n.arrayPos++, n.arrayPos === n.strings.length ? (n.arrayPos = 0, n.options.onLastStringBackspaced(), n.shuffleStringsIfNeeded(), n.begin()) : n.typewrite(n.strings[n.sequence[n.arrayPos]], t))
                            }, i)
                        } else this.setPauseStatus(e, t, !0)
                    }
                }, {
                    key: "complete", value: function () {
                        this.options.onComplete(this), this.loop ? this.curLoop++ : this.typingComplete = !0
                    }
                }, {
                    key: "setPauseStatus", value: function (e, t, n) {
                        this.pause.typewrite = n, this.pause.curString = e, this.pause.curStrPos = t
                    }
                }, {
                    key: "toggleBlinking", value: function (e) {
                        this.cursor && (this.pause.status || this.cursorBlinking !== e && (this.cursorBlinking = e, e ? this.cursor.classList.add("typed-cursor--blink") : this.cursor.classList.remove("typed-cursor--blink")))
                    }
                }, {
                    key: "humanizer", value: function (e) {
                        return Math.round(Math.random() * e / 2) + e
                    }
                }, {
                    key: "shuffleStringsIfNeeded", value: function () {
                        this.shuffle && (this.sequence = this.sequence.sort(function () {
                            return Math.random() - .5
                        }))
                    }
                }, {
                    key: "initFadeOut", value: function () {
                        var e = this;
                        return this.el.className += " " + this.fadeOutClass, this.cursor && (this.cursor.className += " " + this.fadeOutClass), setTimeout(function () {
                            e.arrayPos++, e.replaceText(""), e.strings.length > e.arrayPos ? e.typewrite(e.strings[e.sequence[e.arrayPos]], 0) : (e.typewrite(e.strings[0], 0), e.arrayPos = 0)
                        }, this.fadeOutDelay)
                    }
                }, {
                    key: "replaceText", value: function (e) {
                        this.attr ? this.el.setAttribute(this.attr, e) : this.isInput ? this.el.value = e : "html" === this.contentType ? this.el.innerHTML = e : this.el.textContent = e
                    }
                }, {
                    key: "bindFocusEvents", value: function () {
                        var e = this;
                        this.isInput && (this.el.addEventListener("focus", function (t) {
                            e.stop()
                        }), this.el.addEventListener("blur", function (t) {
                            e.el.value && 0 !== e.el.value.length || e.start()
                        }))
                    }
                }, {
                    key: "insertCursor", value: function () {
                        this.showCursor && (this.cursor || (this.cursor = document.createElement("span"), this.cursor.className = "typed-cursor", this.cursor.innerHTML = this.cursorChar, this.el.parentNode && this.el.parentNode.insertBefore(this.cursor, this.el.nextSibling)))
                    }
                }]), e
            }();
            t.default = s, e.exports = t.default
        }, function (e, t, n) {
            "use strict";
            Object.defineProperty(t, "__esModule", {value: !0});
            var i = Object.assign || function (e) {
                for (var t = 1; t < arguments.length; t++) {
                    var n = arguments[t];
                    for (var i in n) Object.prototype.hasOwnProperty.call(n, i) && (e[i] = n[i])
                }
                return e
            }, o = function () {
                function e(e, t) {
                    for (var n = 0; n < t.length; n++) {
                        var i = t[n];
                        i.enumerable = i.enumerable || !1, i.configurable = !0, "value" in i && (i.writable = !0), Object.defineProperty(e, i.key, i)
                    }
                }

                return function (t, n, i) {
                    return n && e(t.prototype, n), i && e(t, i), t
                }
            }();
            var r = function (e) {
                return e && e.__esModule ? e : {default: e}
            }(n(2)), s = function () {
                function e() {
                    !function (e, t) {
                        if (!(e instanceof t)) throw new TypeError("Cannot call a class as a function")
                    }(this, e)
                }

                return o(e, [{
                    key: "load", value: function (e, t, n) {
                        if (e.el = "string" == typeof n ? document.querySelector(n) : n, e.options = i({}, r.default, t), e.isInput = "input" === e.el.tagName.toLowerCase(), e.attr = e.options.attr, e.bindInputFocusEvents = e.options.bindInputFocusEvents, e.showCursor = !e.isInput && e.options.showCursor, e.cursorChar = e.options.cursorChar, e.cursorBlinking = !0, e.elContent = e.attr ? e.el.getAttribute(e.attr) : e.el.textContent, e.contentType = e.options.contentType, e.typeSpeed = e.options.typeSpeed, e.startDelay = e.options.startDelay, e.backSpeed = e.options.backSpeed, e.smartBackspace = e.options.smartBackspace, e.backDelay = e.options.backDelay, e.fadeOut = e.options.fadeOut, e.fadeOutClass = e.options.fadeOutClass, e.fadeOutDelay = e.options.fadeOutDelay, e.isPaused = !1, e.strings = e.options.strings.map(function (e) {
                            return e.trim()
                        }), "string" == typeof e.options.stringsElement ? e.stringsElement = document.querySelector(e.options.stringsElement) : e.stringsElement = e.options.stringsElement, e.stringsElement) {
                            e.strings = [], e.stringsElement.style.display = "none";
                            var o = Array.prototype.slice.apply(e.stringsElement.children), s = o.length;
                            if (s) for (var a = 0; a < s; a += 1) {
                                var l = o[a];
                                e.strings.push(l.innerHTML.trim())
                            }
                        }
                        for (var a in e.strPos = 0, e.arrayPos = 0, e.stopNum = 0, e.loop = e.options.loop, e.loopCount = e.options.loopCount, e.curLoop = 0, e.shuffle = e.options.shuffle, e.sequence = [], e.pause = {
                            status: !1,
                            typewrite: !0,
                            curString: "",
                            curStrPos: 0
                        }, e.typingComplete = !1, e.strings) e.sequence[a] = a;
                        e.currentElContent = this.getCurrentElContent(e), e.autoInsertCss = e.options.autoInsertCss, this.appendAnimationCss(e)
                    }
                }, {
                    key: "getCurrentElContent", value: function (e) {
                        return e.attr ? e.el.getAttribute(e.attr) : e.isInput ? e.el.value : "html" === e.contentType ? e.el.innerHTML : e.el.textContent
                    }
                }, {
                    key: "appendAnimationCss", value: function (e) {
                        if (e.autoInsertCss && (e.showCursor || e.fadeOut) && !document.querySelector("[data-typed-js-css]")) {
                            var t = document.createElement("style");
                            t.type = "text/css", t.setAttribute("data-typed-js-css", !0);
                            var n = "";
                            e.showCursor && (n += "\n        .typed-cursor{\n          opacity: 1;\n        }\n        .typed-cursor.typed-cursor--blink{\n          animation: typedjsBlink 0.7s infinite;\n          -webkit-animation: typedjsBlink 0.7s infinite;\n                  animation: typedjsBlink 0.7s infinite;\n        }\n        @keyframes typedjsBlink{\n          50% { opacity: 0.0; }\n        }\n        @-webkit-keyframes typedjsBlink{\n          0% { opacity: 1; }\n          50% { opacity: 0.0; }\n          100% { opacity: 1; }\n        }\n      "), e.fadeOut && (n += "\n        .typed-fade-out{\n          opacity: 0;\n          transition: opacity .25s;\n        }\n        .typed-cursor.typed-cursor--blink.typed-fade-out{\n          -webkit-animation: 0;\n          animation: 0;\n        }\n      "), 0 !== t.length && (t.innerHTML = n, document.body.appendChild(t))
                        }
                    }
                }]), e
            }();
            t.default = s;
            var a = new s;
            t.initializer = a
        }, function (e, t) {
            "use strict";
            Object.defineProperty(t, "__esModule", {value: !0});
            var n = {
                strings: ["These are the default values...", "You know what you should do?", "Use your own!", "Have a great day!"],
                stringsElement: null,
                typeSpeed: 0,
                startDelay: 0,
                backSpeed: 0,
                smartBackspace: !0,
                shuffle: !1,
                backDelay: 700,
                fadeOut: !1,
                fadeOutClass: "typed-fade-out",
                fadeOutDelay: 500,
                loop: !1,
                loopCount: 1 / 0,
                showCursor: !0,
                cursorChar: "|",
                autoInsertCss: !0,
                attr: null,
                bindInputFocusEvents: !1,
                contentType: "html",
                onComplete: function (e) {
                },
                preStringTyped: function (e, t) {
                },
                onStringTyped: function (e, t) {
                },
                onLastStringBackspaced: function (e) {
                },
                onTypingPaused: function (e, t) {
                },
                onTypingResumed: function (e, t) {
                },
                onReset: function (e) {
                },
                onStop: function (e, t) {
                },
                onStart: function (e, t) {
                },
                onDestroy: function (e) {
                }
            };
            t.default = n, e.exports = t.default
        }, function (e, t) {
            "use strict";
            Object.defineProperty(t, "__esModule", {value: !0});
            var n = function () {
                function e(e, t) {
                    for (var n = 0; n < t.length; n++) {
                        var i = t[n];
                        i.enumerable = i.enumerable || !1, i.configurable = !0, "value" in i && (i.writable = !0), Object.defineProperty(e, i.key, i)
                    }
                }

                return function (t, n, i) {
                    return n && e(t.prototype, n), i && e(t, i), t
                }
            }();
            var i = function () {
                function e() {
                    !function (e, t) {
                        if (!(e instanceof t)) throw new TypeError("Cannot call a class as a function")
                    }(this, e)
                }

                return n(e, [{
                    key: "typeHtmlChars", value: function (e, t, n) {
                        if ("html" !== n.contentType) return t;
                        var i = e.substr(t).charAt(0);
                        if ("<" === i || "&" === i) {
                            var o = "";
                            for (o = "<" === i ? ">" : ";"; e.substr(t + 1).charAt(0) !== o && !(++t + 1 > e.length);) ;
                            t++
                        }
                        return t
                    }
                }, {
                    key: "backSpaceHtmlChars", value: function (e, t, n) {
                        if ("html" !== n.contentType) return t;
                        var i = e.substr(t).charAt(0);
                        if (">" === i || ";" === i) {
                            var o = "";
                            for (o = ">" === i ? "<" : "&"; e.substr(t - 1).charAt(0) !== o && !(--t < 0);) ;
                            t--
                        }
                        return t
                    }
                }]), e
            }();
            t.default = i;
            var o = new i;
            t.htmlParser = o
        }])
    })
}, function (e, t, n) {
    n(64), n(65), n(66), n(67), n(68), n(69), n(70), n(71), n(72), n(73), n(74), n(75), n(76), n(77), n(78), n(79), n(80)
}, function (e, t) {
    !function (e) {
        page.initBind = function () {
            e("[data-bind-radio]").each(function () {
                var t = e(this), n = t.data("bind-radio"), i = e('input[name="' + n + '"]:checked').val();
                t.text(t.dataAttr(i, t.text())), e('input[name="' + n + '"]').on("change", function () {
                    var t = e('input[name="' + n + '"]:checked').val();
                    e('[data-bind-radio="' + n + '"]').each(function () {
                        var n = e(this);
                        n.text(n.dataAttr(t, n.text()))
                    })
                })
            }), e("[data-bind-href]").each(function () {
                var t = e(this), n = t.data("bind-href"), i = e('input[name="' + n + '"]:checked').val();
                t.attr("href", t.dataAttr(i)), e('input[name="' + n + '"]').on("change", function () {
                    var t = e('input[name="' + n + '"]:checked').val();
                    e('[data-bind-href="' + n + '"]').each(function () {
                        var n = e(this);
                        n.attr("href", n.dataAttr(t))
                    })
                })
            })
        }
    }(jQuery)
}, function (e, t) {
    !function (e) {
        page.initDrawer = function () {
            e(document).on("click", ".drawer-toggler, .drawer-close, .backdrop-drawer", function () {
                e("body").toggleClass("drawer-open")
            })
        }
    }(jQuery)
}, function (e, t) {
    !function (e) {
        page.initFont = function () {
            var t = [];
            e("[data-font]").each(function () {
                var n = e(this), i = n.data("font");
                part = i.split(":"), t.push(i), n.css({"font-family": part[0], "font-weight": part[1]})
            }), t.length > 0 && e("head").append("<link href='https://fonts.googleapis.com/css?family=" + t.join("|") + "' rel='stylesheet'>")
        }
    }(jQuery)
}, function (e, t) {
    !function (e) {
        page.initForm = function () {
            e(document).on("focusin", ".input-group", function () {
                e(this).addClass("focus")
            }), e(document).on("focusout", ".input-group", function () {
                e(this).removeClass("focus")
            }), e(document).on("click", ".file-browser", function () {
                var t = e(this), n = t.closest(".file-group").find('[type="file"]');
                t.hasClass("form-control") ? setTimeout(function () {
                    n.trigger("click")
                }, 300) : n.trigger("click")
            }), e(document).on("change", '.file-group [type="file"]', function () {
                var t = e(this), n = t.val().split("\\").pop();
                t.closest(".file-group").find(".file-value").val(n).text(n).focus()
            })
        }
    }(jQuery)
}, function (e, t) {
    !function (e) {
        page.initMailer = function () {
            var t = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            e('[data-form="mailer"]').each(function () {
                var n = e(this), i = n.find('[name="email"]'), o = n.find('[name="message"]');
                n.on("submit", function () {
                    return n.children(".alert-danger").remove(), i.length && (i.val().length < 1 || !t.test(i.val())) ? (i.addClass("is-invalid"), !1) : o.length && o.val().length < 1 ? (o.addClass("is-invalid"), !1) : (e.ajax({
                        type: "POST",
                        url: n.attr("action"),
                        data: n.serializeArray()
                    }).done(function (t) {
                        var i = e.parseJSON(t);
                        "success" == i.status ? (n.find(".alert-success").fadeIn(1e3), n.find(":input").val("")) : (n.prepend('<div class="alert alert-danger">' + i.message + "</div>"), console.log(i.reason))
                    }), !1)
                }), i.on("focus", function () {
                    e(this).removeClass("is-invalid")
                }), o.on("focus", function () {
                    e(this).removeClass("is-invalid")
                })
            })
        }
    }(jQuery)
}, function (e, t) {
    !function (e) {
        page.initMap = function () {
            e('[data-provide~="map"]').each(function () {
                var t = e(this), n = {
                    lat: "",
                    lng: "",
                    zoom: 13,
                    markerLat: "",
                    markerLng: "",
                    markerIcon: "",
                    markers: "",
                    style: "",
                    removeControls: !1
                };
                n = e.extend(n, page.getDataOptions(t));
                var i = new google.maps.Map(t[0], {
                    center: {lat: Number(n.lat), lng: Number(n.lng)},
                    zoom: Number(n.zoom),
                    disableDefaultUI: n.removeControls
                });
                if ("" != n.markers) {
                    var o, r = JSON.parse("[" + n.markers.replace(/'/g, '"') + "]"), s = new google.maps.InfoWindow;
                    for (o = 0; o < r.length; o++) {
                        var a = n.markerIcon;
                        r[o].length > 3 && "" != r[o][3] && (a = r[o][3]), l = new google.maps.Marker({
                            position: {
                                lat: Number(r[o][0]),
                                lng: Number(r[o][1])
                            }, map: i, animation: google.maps.Animation.DROP, icon: a
                        }), r[o].length > 2 && "" != r[o][2] && google.maps.event.addListener(l, "click", function (e, t) {
                            return function () {
                                s.setContent(r[t][2]), s.open(i, e)
                            }
                        }(l, o))
                    }
                } else {
                    var l = new google.maps.Marker({
                        position: {lat: Number(n.markerLat), lng: Number(n.markerLng)},
                        map: i,
                        animation: google.maps.Animation.DROP,
                        icon: n.markerIcon
                    });
                    if (t.is("[data-info]")) {
                        s = new google.maps.InfoWindow({content: t.dataAttr("info", "")});
                        l.addListener("click", function () {
                            s.open(i, l)
                        })
                    }
                }
                switch (n.style) {
                    case"light":
                        i.set("styles", [{
                            featureType: "water",
                            elementType: "geometry",
                            stylers: [{color: "#e9e9e9"}, {lightness: 17}]
                        }, {
                            featureType: "landscape",
                            elementType: "geometry",
                            stylers: [{color: "#f5f5f5"}, {lightness: 20}]
                        }, {
                            featureType: "road.highway",
                            elementType: "geometry.fill",
                            stylers: [{color: "#ffffff"}, {lightness: 17}]
                        }, {
                            featureType: "road.highway",
                            elementType: "geometry.stroke",
                            stylers: [{color: "#ffffff"}, {lightness: 29}, {weight: .2}]
                        }, {
                            featureType: "road.arterial",
                            elementType: "geometry",
                            stylers: [{color: "#ffffff"}, {lightness: 18}]
                        }, {
                            featureType: "road.local",
                            elementType: "geometry",
                            stylers: [{color: "#ffffff"}, {lightness: 16}]
                        }, {
                            featureType: "poi",
                            elementType: "geometry",
                            stylers: [{color: "#f5f5f5"}, {lightness: 21}]
                        }, {
                            featureType: "poi.park",
                            elementType: "geometry",
                            stylers: [{color: "#dedede"}, {lightness: 21}]
                        }, {
                            elementType: "labels.text.stroke",
                            stylers: [{visibility: "on"}, {color: "#ffffff"}, {lightness: 16}]
                        }, {
                            elementType: "labels.text.fill",
                            stylers: [{saturation: 36}, {color: "#333333"}, {lightness: 40}]
                        }, {elementType: "labels.icon", stylers: [{visibility: "off"}]}, {
                            featureType: "transit",
                            elementType: "geometry",
                            stylers: [{color: "#f2f2f2"}, {lightness: 19}]
                        }, {
                            featureType: "administrative",
                            elementType: "geometry.fill",
                            stylers: [{color: "#fefefe"}, {lightness: 20}]
                        }, {
                            featureType: "administrative",
                            elementType: "geometry.stroke",
                            stylers: [{color: "#fefefe"}, {lightness: 17}, {weight: 1.2}]
                        }]);
                        break;
                    case"dark":
                        i.set("styles", [{
                            featureType: "all",
                            elementType: "labels.text.fill",
                            stylers: [{saturation: 36}, {color: "#000000"}, {lightness: 40}]
                        }, {
                            featureType: "all",
                            elementType: "labels.text.stroke",
                            stylers: [{visibility: "on"}, {color: "#000000"}, {lightness: 16}]
                        }, {
                            featureType: "all",
                            elementType: "labels.icon",
                            stylers: [{visibility: "off"}]
                        }, {
                            featureType: "administrative",
                            elementType: "geometry.fill",
                            stylers: [{color: "#000000"}, {lightness: 20}]
                        }, {
                            featureType: "administrative",
                            elementType: "geometry.stroke",
                            stylers: [{color: "#000000"}, {lightness: 17}, {weight: 1.2}]
                        }, {
                            featureType: "landscape",
                            elementType: "geometry",
                            stylers: [{color: "#000000"}, {lightness: 20}]
                        }, {
                            featureType: "poi",
                            elementType: "geometry",
                            stylers: [{color: "#000000"}, {lightness: 21}]
                        }, {
                            featureType: "road.highway",
                            elementType: "geometry.fill",
                            stylers: [{color: "#000000"}, {lightness: 17}]
                        }, {
                            featureType: "road.highway",
                            elementType: "geometry.stroke",
                            stylers: [{color: "#000000"}, {lightness: 29}, {weight: .2}]
                        }, {
                            featureType: "road.arterial",
                            elementType: "geometry",
                            stylers: [{color: "#000000"}, {lightness: 18}]
                        }, {
                            featureType: "road.local",
                            elementType: "geometry",
                            stylers: [{color: "#000000"}, {lightness: 16}]
                        }, {
                            featureType: "transit",
                            elementType: "geometry",
                            stylers: [{color: "#000000"}, {lightness: 19}]
                        }, {
                            featureType: "water",
                            elementType: "geometry",
                            stylers: [{color: "#000000"}, {lightness: 17}]
                        }]);
                        break;
                    default:
                        Array.isArray(n.style) && i.set("styles", n.style)
                }
            })
        }
    }(jQuery)
}, function (e, t) {
    !function (e) {
        page.initModal = function () {
            page.body;
            e(".modal[data-autoshow]").each(function () {
                var t = e(this), n = parseInt(t.dataAttr("autoshow"));
                setTimeout(function () {
                    t.modal("show")
                }, n)
            }), e(".modal[data-exitshow]").each(function () {
                var t = e(this), n = parseInt(t.dataAttr("delay", 0)), i = t.dataAttr("exitshow");
                e(i).length && e(document).one("mouseleave", i, function () {
                    setTimeout(function () {
                        t.modal("show")
                    }, n)
                })
            })
        }
    }(jQuery)
}, function (e, t) {
    !function (e) {
        page.initNavbar = function () {
            e(document).on("click", ".navbar-toggler", function () {
                page.navbarToggle()
            }), e(document).on("click", ".backdrop-navbar", function () {
                page.navbarClose()
            }), e(document).on("click", ".navbar-open .nav-navbar > .nav-item > .nav-link", function () {
                e(this).closest(".nav-item").siblings(".nav-item").find("> .nav:visible").slideUp(333, "linear"), e(this).next(".nav").slideToggle(333, "linear")
            })
        }, page.navbarToggle = function () {
            var e = page.body, t = page.navbar;
            e.toggleClass("navbar-open"), e.hasClass("navbar-open") && t.prepend('<div class="backdrop backdrop-navbar"></div>')
        }, page.navbarClose = function () {
            page.body.removeClass("navbar-open"), e(".backdrop-navbar").remove()
        }
    }(jQuery)
}, function (e, t) {
    !function (e) {
        page.initOffcanvas = function () {
            e(document).on("click", '[data-toggle="offcanvas"]', function () {
                var t = e(this).data("target"), n = e(t);
                void 0 !== t && n.length && (n.hasClass("show") ? (e(".backdrop-offcanvas").remove(), n.removeClass("show")) : (n.before('<div class="backdrop backdrop-offcanvas"></div>'), n.addClass("show"), setTimeout(function () {
                    n.find("input:text:visible:first").focus()
                }, 300)))
            }), e(document).on("click", ".offcanvas [data-dismiss], .backdrop-offcanvas", function () {
                e(".offcanvas.show").removeClass("show"), e(".backdrop-offcanvas").remove()
            }), e(document).on("keyup", function (t) {
                e(".offcanvas.show").length && 27 == t.keyCode && (e(".offcanvas.show").removeClass("show"), e(".backdrop-offcanvas").remove())
            })
        }
    }(jQuery)
}, function (e, t) {
    !function (e) {
        page.initPopup = function () {
            page.body;
            e(document).on("click", '[data-toggle="popup"]', function () {
                var n = e(this).data("target"), i = e(n);
                void 0 !== n && i.length && (i.hasClass("show") ? i.removeClass("show") : t(i))
            }), e(document).on("click", ".popup [data-dismiss]", function () {
                e(this).closest(".popup").removeClass("show")
            }), e(".popup[data-autoshow]").each(function () {
                var n = e(this), i = parseInt(n.dataAttr("autoshow"));
                setTimeout(function () {
                    t(n)
                }, i)
            }), e(".popup[data-exitshow]").each(function () {
                var n = e(this), i = parseInt(n.dataAttr("delay", 0)), o = n.dataAttr("exitshow");
                e(o).length && e(document).one("mouseleave", o, function () {
                    setTimeout(function () {
                        t(n)
                    }, i)
                })
            });
            var t = function (e) {
                var t = parseInt(e.dataAttr("autohide", 0)), n = e.dataAttr("once", "");
                if ("" != n) {
                    if ("displayed" == localStorage.getItem(n)) return;
                    var i = e.find('[data-once-button="true"]');
                    i.length ? i.on("click", function () {
                        localStorage.setItem(n, "displayed")
                    }) : localStorage.setItem(n, "displayed")
                }
                e.addClass("show"), setTimeout(function () {
                    e.find("input:text:visible:first").focus()
                }, 300), t > 0 && setTimeout(function () {
                    e.removeClass("show")
                }, t)
            }
        }
    }(jQuery)
}, function (e, t) {
    !function (e) {
        page.initRecaptcha = function () {
            e('[data-provide~="recaptcha"]').each(function () {
                var t = {sitekey: page.defaults.reCaptchaSiteKey};
                (t = e.extend(t, page.getDataOptions(e(this)))).enable && (t.callback = function () {
                    e(t.enable).removeAttr("disabled")
                }, t["expired-callback"] = function () {
                    e(t.enable).attr("disabled", "true")
                }), grecaptcha.render(e(this)[0], t)
            })
        }, window.recaptchaLoadCallback = function () {
            page.initRecaptcha()
        }
    }(jQuery)
}, function (e, t) {
    !function (e) {
        var t = page.body, n = page.footer, i = page.header.length, o = page.navbar.outerHeight(),
            r = page.header.innerHeight(), s = 0, a = 0;
        page.initScroll = function () {
            e('[data-navbar="fixed"], [data-navbar="sticky"], [data-navbar="smart"]').length && (s = o), e(document).on("click", "a[href='#']", function () {
                return !1
            }), e(document).on("click", ".scroll-top", function () {
                return c(0), !1
            }), e(document).on("click", "a[href^='#']", function () {
                if (!(e(this).attr("href").length < 2 || e(this)[0].hasAttribute("data-toggle"))) {
                    var n = e(e(this).attr("href"));
                    if (n.length) {
                        var i = n.offset().top;
                        return i > e(window).scrollTop() && e('.navbar[data-navbar="smart"]').length ? c(i) : c(i - s), t.hasClass("navbar-open") && page.navbarClose(), !1
                    }
                }
            });
            var n = location.hash.replace("#", "");
            if ("" != n) {
                var i = e("#" + n);
                i.length > 0 && c(i.offset().top - s)
            }
            if (l(), e(window).on("scroll", function () {
                l()
            }), e(".nav-page").length) {
                var r = "left", a = "0, 12";
                e(".nav-page.nav-page-left").length && (r = "right", a = "0, 12");
                var u = parseInt(e(".nav-page").dataAttr("spy-offset", 200));
                e(".nav-page .nav-link").tooltip({
                    container: "body",
                    placement: r,
                    offset: a,
                    trigger: "hover"
                }), e("body").scrollspy({target: ".nav-page", offset: u})
            }
            e(".sidebar-sticky").each(function () {
                var n = e(this), i = n.closest("div").width();
                n.css("width", i), t.width() / i < 1.8 && n.addClass("is-mobile-wide")
            })
        };
        var l = function () {
            var i = e(window).scrollTop();
            i > 1 ? t.addClass("body-scrolled") : t.removeClass("body-scrolled"), i > o ? t.addClass("navbar-scrolled") : t.removeClass("navbar-scrolled"), i > r - o - 1 ? t.addClass("header-scrolled") : t.removeClass("header-scrolled"), e('[data-sticky="true"]').each(function () {
                var t = e(this), o = t.offset().top;
                t.hasDataAttr("original-top") || t.attr("data-original-top", o);
                var r = t.dataAttr("original-top");
                n.offset().top, t.outerHeight();
                i > r ? t.addClass("stick") : t.removeClass("stick")
            }), e('[data-navbar="smart"]').each(function () {
                var t = e(this);
                i < a ? u(t) : t.removeClass("stick")
            }), e('[data-navbar="sticky"]').each(function () {
                var t = e(this);
                u(t)
            }), e('[data-navbar="fixed"]').each(function () {
                var n = e(this);
                t.hasClass("body-scrolled") ? n.addClass("stick") : n.removeClass("stick")
            }), e(".sidebar-sticky").each(function () {
                var t = e(this);
                u(t)
            }), e(".header.fadeout").css("opacity", 1 - i - 200 / window.innerHeight), a = i
        }, c = function (t) {
            e("html, body").animate({scrollTop: t}, 600)
        }, u = function (e) {
            var n = "navbar-scrolled";
            i && (n = "header-scrolled"), t.hasClass(n) ? e.addClass("stick") : e.removeClass("stick")
        }
    }(jQuery)
}, function (e, t) {
    jQuery, page.initSection = function () {
    }
}, function (e, t) {
    !function (e) {
        page.initSidebar = function () {
            var t = page.body;
            e(document).on("click", ".sidebar-toggler, .sidebar-close, .backdrop-sidebar", function () {
                t.toggleClass("sidebar-open"), t.hasClass("sidebar-open") ? t.prepend('<div class="backdrop backdrop-sidebar"></div>') : e(".backdrop-sidebar").remove()
            });
            var n = e(".nav-sidebar .nav-item.show");
            n.find("> .nav-link .nav-angle").addClass("rotate"), n.find("> .nav").css("display", "block"), n.removeClass("show");
            var i = !1;
            "true" == e(".nav-sidebar").dataAttr("accordion", "false") && (i = !0), e(document).on("click", ".nav-sidebar > .nav-item > .nav-link", function () {
                var t = e(this);
                t.next(".nav").slideToggle(), i && t.closest(".nav-item").siblings(".nav-item").children(".nav:visible").slideUp().prev(".nav-link").children(".nav-angle").removeClass("rotate"), t.children(".nav-angle").toggleClass("rotate")
            }), e(".sidebar-body").each(function (t) {
                new PerfectScrollbar(e(this)[0], {wheelSpeed: .4, minScrollbarLength: 20})
            })
        }
    }(jQuery)
}, function (e, t) {
    !function (e) {
        page.initVideo = function () {
            e(document).on("click", ".video-wrapper .btn", function () {
                var t = e(this).closest(".video-wrapper");
                if (t.addClass("reveal"), t.find("video").length && t.find("video").get(0).play(), t.find("iframe").length) {
                    var n = t.find("iframe");
                    n.attr("src").indexOf("?") > 0 ? n.get(0).src += "&autoplay=1" : n.get(0).src += "?autoplay=1"
                }
            }), objectFitPolyfill(e(".bg-video"))
        }
    }(jQuery)
}, function (e, t) {
    !function (e) {
        page.getDataOptions = function (t, n) {
            var i = {};
            return e.each(e(t).data(), function (e, t) {
                if ("provide" != (e = page.dataToOption(e))) {
                    if (void 0 != n) switch (n[e]) {
                        case"bool":
                            t = Boolean(t);
                            break;
                        case"num":
                            t = Number(t);
                            break;
                        case"array":
                            t = t.split(",")
                    }
                    i[e] = t
                }
            }), i
        }, page.getTarget = function (t) {
            var n;
            return "next" == (n = t.hasDataAttr("target") ? t.data("target") : t.attr("href")) ? n = e(t).next() : "prev" == n && (n = e(t).prev()), void 0 != n && n
        }, page.getURL = function (e) {
            return e.hasDataAttr("url") ? e.data("url") : e.attr("href")
        }, page.optionToData = function (e) {
            return e.replace(/([A-Z])/g, "-$1").toLowerCase()
        }, page.dataToOption = function (e) {
            return e.replace(/-([a-z])/g, function (e) {
                return e[1].toUpperCase()
            })
        }
    }(jQuery)
}]);

'use strict';

$(function () {


    /*
    |--------------------------------------------------------------------------
    | Configure your website
    |--------------------------------------------------------------------------
    |
    | We provided several configuration variables for your ease of development.
    | Read their complete description and modify them based on your need.
    |
    */

    page.config({


        /*
        |--------------------------------------------------------------------------
        | Disable AOS on mobile
        |--------------------------------------------------------------------------
        |
        | If true, the Animate On Scroll animations don't run on mobile devices.
        |
        */

        disableAOSonMobile: true,

        /*
        |--------------------------------------------------------------------------
        | Smooth Scroll
        |--------------------------------------------------------------------------
        |
        | If true, the browser's scrollbar moves smoothly on scroll and gives your
        | visitor a better experience for scrolling.
        |
        */

        smoothScroll: true,

    });
});

