// The vector artwork is the original PDF layout. Fit each selectable HTML line
// to its PDF bounds without changing the visible document or its typography.
const page = document.querySelector('.pdf-page');
const lines = [...document.querySelectorAll('.text-line')];
let frame;
function fitText() {
  cancelAnimationFrame(frame);
  frame = requestAnimationFrame(() => {
    for (const line of lines) line.firstElementChild.style.transform = 'none';
    const sizes = lines.map(line => ({
      text: line.firstElementChild,
      target: line.getBoundingClientRect(),
      actual: line.firstElementChild.getBoundingClientRect(),
    }));
    for (const { text, target, actual } of sizes) {
      if (actual.width && actual.height) {
        text.style.transform = `scale(${target.width / actual.width}, ${target.height / actual.height})`;
      }
    }
  });
}
new ResizeObserver(fitText).observe(page);
document.fonts.ready.then(fitText);
const printButton = document.getElementById('print-resume');
printButton.hidden = false;
printButton.addEventListener('click', () => window.print());
