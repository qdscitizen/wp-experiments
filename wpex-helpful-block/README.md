# WPEx Helpful Block

A lightweight WordPress block that adds a "Was this helpful?" voting widget to the end of every post.

## How it works

- Uses the **Block Hooks API** (`core/post-content: lastChild`) so the block is auto-appended to every post — zero configuration, nothing to place manually.
- Visitors vote 👍 / 👎. The vote is POSTed to a custom REST endpoint (`/wpex-helpful/v1/vote`) with a nonce, and counts are stored in post meta (`wpex_helpful_up` / `wpex_helpful_down`).
- When a post is displayed, its content goes through the `the_content` filter pipeline — `do_blocks` parses block markup at priority 9, and this block renders at priority 8 via the hook, before parsing.

## Files

| File | Role |
|------|------|
| `wpex-helpful-block.php` | Main file: block registration, REST route, asset enqueue |
| `block.json` | Block metadata + Block Hooks rule |
| `assets/helpful.js` | Front-end vote handling (fetch + nonce) |
| `assets/helpful.css` | Minimal styling |

## Status

V0.1 — working prototype.
