/**
 * Men Let's Talk Interactive Behaviour:
 *   1. Mobile Navigation Drawer (toggle, Escape close, outside-click close)
 *   2. Subscribe Form (/api/subscribe)
 *   3. Event Registration Form (/api/register)
 *   4. Share Your Story Form (/api/share-story)
 *   5. Contact Form with 7-layer anti-spam challenge (/api/contact)
 */
(function () {
  "use strict";

  // 1. Mobile Navigation Drawer
  var header = document.getElementById("mlt-site-header");
  var toggleBtn = document.querySelector('[data-testid="mobile-menu-toggle"]');
  var mobileNav = document.querySelector('[data-testid="mobile-nav"]');

  function isMenuOpen() {
    return mobileNav && mobileNav.style.display !== "none";
  }

  function setMenuOpen(open) {
    if (!mobileNav || !toggleBtn) return;
    mobileNav.style.display = open ? "flex" : "none";
    toggleBtn.setAttribute("aria-label", open ? "Close menu" : "Open menu");
    toggleBtn.setAttribute("aria-expanded", open ? "true" : "false");
  }

  if (toggleBtn && mobileNav) {
    toggleBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      setMenuOpen(!isMenuOpen());
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && isMenuOpen()) {
        setMenuOpen(false);
      }
    });

    document.addEventListener("click", function (e) {
      if (isMenuOpen() && header && !header.contains(e.target)) {
        setMenuOpen(false);
      }
    });
  }

  // 2. Subscribe Form
  var subscribeWidgets = document.querySelectorAll(".mlt-subscribe-widget");
  subscribeWidgets.forEach(function (widget) {
    var form = widget.querySelector(".mlt-subscribe-form");
    var errorEl = widget.querySelector(".mlt-subscribe-error");
    var successEl = widget.querySelector(".mlt-subscribe-success");
    var submitBtn = widget.querySelector(".mlt-subscribe-submit");
    if (!form) return;

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      if (errorEl) {
        errorEl.style.display = "none";
        errorEl.textContent = "";
      }
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = "Subscribing…";
      }

      var fd = new FormData(form);
      var interests = ["events", "resources", "conversations", "newsletter"].filter(function (k) {
        return fd.get(k) === "on";
      });

      fetch("/api/subscribe", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          email: fd.get("email"),
          interests: interests,
          consentGiven: fd.get("consentGiven") === "on",
        }),
      })
        .then(function (res) {
          return res.json().then(function (data) {
            if (!res.ok) throw new Error(data.error || "Something went wrong.");
            form.style.display = "none";
            if (successEl) successEl.style.display = "block";
          });
        })
        .catch(function (err) {
          if (errorEl) {
            errorEl.textContent = err.message || "Something went wrong.";
            errorEl.style.display = "block";
          }
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = "Subscribe";
          }
        });
    });
  });

  // 3. Event Registration Form
  var registerWidgets = document.querySelectorAll(".mlt-register-widget");
  registerWidgets.forEach(function (widget) {
    var eventId = widget.getAttribute("data-event-id");
    var openBtn = widget.querySelector(".mlt-register-open-btn");
    var form = widget.querySelector(".mlt-register-form");
    var errorEl = widget.querySelector(".mlt-register-error");
    var successEl = widget.querySelector(".mlt-register-success");
    var submitBtn = widget.querySelector(".mlt-register-submit");

    if (openBtn && form) {
      openBtn.addEventListener("click", function () {
        openBtn.style.display = "none";
        form.style.display = "block";
      });

      form.addEventListener("submit", function (e) {
        e.preventDefault();
        if (errorEl) {
          errorEl.style.display = "none";
          errorEl.textContent = "";
        }
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.textContent = "Submitting…";
        }

        var fd = new FormData(form);
        fetch("/api/register", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            eventId: eventId,
            name: fd.get("name"),
            email: fd.get("email"),
            phone: fd.get("phone"),
            consentGiven: fd.get("consentGiven") === "on",
          }),
        })
          .then(function (res) {
            return res.json().then(function (data) {
              if (!res.ok) throw new Error(data.error || "Something went wrong.");
              form.style.display = "none";
              if (successEl) successEl.style.display = "block";
            });
          })
          .catch(function (err) {
            if (errorEl) {
              errorEl.textContent = err.message || "Something went wrong.";
              errorEl.style.display = "block";
            }
            if (submitBtn) {
              submitBtn.disabled = false;
              submitBtn.textContent = "Confirm Registration";
            }
          });
      });
    }
  });

  // 4. Share Your Story Form
  var shareStoryForm = document.querySelector('[data-testid="share-story-form"]');
  if (shareStoryForm) {
    var storyError = document.querySelector('[data-testid="share-story-error"]');
    var storySuccess = document.querySelector('[data-testid="share-story-success"]');
    var storySubmit = shareStoryForm.querySelector('button[type="submit"]');
    var topicKeys = [
      "pressure",
      "failure",
      "marriage",
      "fatherhood",
      "money",
      "purpose",
      "loneliness",
      "grief",
      "relationships",
      "mental-health",
      "masculinity",
      "faith",
      "starting-again",
    ];

    shareStoryForm.addEventListener("submit", function (e) {
      e.preventDefault();
      if (storyError) {
        storyError.style.display = "none";
        storyError.textContent = "";
      }
      if (storySubmit) {
        storySubmit.disabled = true;
        storySubmit.textContent = "Submitting…";
      }

      var fd = new FormData(shareStoryForm);
      var topics = topicKeys.filter(function (k) {
        return fd.get(k) === "on";
      });

      fetch("/api/share-story", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          name: fd.get("name") || undefined,
          ageOrCategory: fd.get("ageOrCategory") || undefined,
          topics: topics,
          storyText: fd.get("storyText") || undefined,
          videoUrl: fd.get("videoUrl") || undefined,
          processingConsentGiven: fd.get("processingConsentGiven") === "on",
          consentGiven: fd.get("consentGiven") === "on",
        }),
      })
        .then(function (res) {
          return res.json().then(function (data) {
            if (!res.ok) throw new Error(data.error || "Something went wrong.");
            shareStoryForm.style.display = "none";
            if (storySuccess) storySuccess.style.display = "block";
          });
        })
        .catch(function (err) {
          if (storyError) {
            storyError.textContent = err.message || "Something went wrong.";
            storyError.style.display = "block";
          }
          if (storySubmit) {
            storySubmit.disabled = false;
            storySubmit.textContent = "Share Your Story";
          }
        });
    });
  }

  // 5. Contact Form with 7-layer anti-spam challenge
  var contactForm = document.querySelector('[data-testid="contact-form"]');
  if (contactForm) {
    var contactError = document.querySelector('[data-testid="contact-error"]');
    var contactSuccess = document.querySelector('[data-testid="contact-form-submitted"]');
    var contactSubmit = contactForm.querySelector('button[type="submit"]');
    var challenge = { ts: "", seed: "", sig: "" };
    var interacted = false;

    function markInteracted() {
      interacted = true;
    }

    function buildJsToken(ts, seed) {
      return seed.split("").reverse().join("") + "-" + ts.slice(-4);
    }

    ["focusin", "keydown", "pointerdown", "touchstart"].forEach(function (evt) {
      contactForm.addEventListener(evt, markInteracted);
    });

    fetch("/api/contact", { cache: "no-store" })
      .then(function (res) {
        return res.ok ? res.json() : null;
      })
      .then(function (data) {
        if (data && typeof data.ts === "string") {
          challenge = { ts: data.ts, seed: data.seed, sig: data.sig };
        }
      })
      .catch(function () {});

    contactForm.addEventListener("submit", function (e) {
      e.preventDefault();
      if (contactError) {
        contactError.style.display = "none";
        contactError.textContent = "";
      }
      if (contactSubmit) {
        contactSubmit.disabled = true;
        contactSubmit.textContent = "Sending…";
      }

      var fd = new FormData(contactForm);
      var jsToken =
        interacted && challenge.ts && challenge.seed
          ? buildJsToken(challenge.ts, challenge.seed)
          : "";

      fetch("/api/contact", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          name: fd.get("name"),
          email: fd.get("email"),
          phone: fd.get("phone"),
          topic: fd.get("topic"),
          message: fd.get("message"),
          website: fd.get("website"),
          ts: challenge.ts,
          seed: challenge.seed,
          sig: challenge.sig,
          jsToken: jsToken,
        }),
      })
        .then(function (res) {
          return res.json().then(function (data) {
            if (!res.ok) throw new Error(data.error || "Something went wrong.");
            contactForm.style.display = "none";
            if (contactSuccess) contactSuccess.style.display = "block";
          });
        })
        .catch(function (err) {
          if (contactError) {
            contactError.textContent = err.message || "Something went wrong.";
            contactError.style.display = "block";
          }
          if (contactSubmit) {
            contactSubmit.disabled = false;
            contactSubmit.textContent = "Send";
          }
        });
    });
  }
})();
