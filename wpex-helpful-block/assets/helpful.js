document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.wpex-helpful__btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var wrap = btn.closest('.wpex-helpful');
      var postId = wrap.dataset.postId;
      var vote = btn.dataset.vote;

      fetch(wpexHelpful.restUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': wpexHelpful.nonce
        },
        body: JSON.stringify({ post_id: Number(postId), vote: vote })
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res || !res.success) return;
          var el = wrap.querySelector('.wpex-helpful__count-' + vote);
          if (el) el.textContent = res.count;
          wrap.querySelectorAll('.wpex-helpful__btn').forEach(function (b) { b.disabled = true; });
          btn.classList.add('is-voted');
        });
    });
  });
});
