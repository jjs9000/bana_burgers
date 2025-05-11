/**
 * Creates and injects an SVG tick animation into a container element
 * @param {HTMLElement} container - The element to inject the animation into
 * @param {Object} options - Animation options
 * @param {string} options.circleColor - The color of the circle (default: #1C9943)
 * @param {string} options.tickColor - The color of the tick mark (default: #1C9943)
 * @param {number} options.strokeWidth - The stroke width (default: 10)
 * @param {Function} options.onComplete - Callback function when animation is complete
 */
function createTickAnimation(container, options = {}) {
  if (!container) {
    console.error('No container element provided for tick animation');
    return;
  }

  // Default options
  const settings = {
    circleColor: options.circleColor || '#1C9943',
    tickColor: options.tickColor || '#1C9943',
    strokeWidth: options.strokeWidth || 10,
    onComplete: options.onComplete || function() {}
  };

  // Clear the container
  container.innerHTML = '';
  container.classList.add('tick-animation-container');

  // Create the SVG element
  const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  svg.setAttribute('version', '1.1');
  svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
  svg.setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
  svg.setAttribute('xml:space', 'preserve');
  svg.classList.add('tick-animation-svg');
  svg.setAttribute('viewBox', '0 0 300 300');

  // Create the circle path
  const circle = document.createElementNS('http://www.w3.org/2000/svg', 'path');
  circle.classList.add('tick-circle');
  circle.setAttribute('stroke', settings.circleColor);
  circle.setAttribute('stroke-width', settings.strokeWidth);
  circle.setAttribute('fill', '#fff');
  circle.setAttribute('fill-opacity', '0');
  circle.setAttribute('stroke-miterlimit', '10');
  circle.setAttribute('d', 'M150,47.9c18.4,0,35.4,4.6,51,13.8s28,21.6,37.2,37.2s13.8,32.6,13.8,51s-4.6,35.4-13.8,51s-21.6,28-37.2,37.2s-32.6,13.8-51,13.8s-35.4-4.6-51-13.8s-28-21.6-37.2-37.2s-13.8-32.6-13.8-51s4.6-35.4,13.8-51s21.6-28,37.2-37.2S131.7,47.9,150,47.9z');

  // Create the tick mark path
  const tick = document.createElementNS('http://www.w3.org/2000/svg', 'path');
  tick.classList.add('tick-mark');
  tick.setAttribute('fill', settings.tickColor);
  tick.setAttribute('stroke', '');
  tick.setAttribute('stroke-width', settings.strokeWidth);
  tick.setAttribute('d', 'M208.4,118.6c0.8-0.8,1.2-1.9,1.2-3.3c0-1.4-0.4-2.6-1.2-3.7l-3.7-3.3c-0.8-1.1-1.9-1.6-3.3-1.6s-2.6,0.4-3.7,1.2l-67,67l-28.4-28.8c-1.1-0.8-2.3-1.2-3.7-1.2c-1.4,0-2.5,0.4-3.3,1.2l-3.7,3.3c-0.8,1.1-1.2,2.3-1.2,3.7s0.4,2.5,1.2,3.3l35.4,35.8c1.1,1.1,2.3,1.6,3.7,1.6c1.4,0,2.5-0.5,3.3-1.6L208.4,118.6z');

  // Add elements to the SVG
  svg.appendChild(circle);
  svg.appendChild(tick);

  // Add SVG to the container
  container.appendChild(svg);

  // Set up the complete callback
  tick.addEventListener('animationend', settings.onComplete);

  return svg;
}

// If we're in a browser environment, add to window
if (typeof window !== 'undefined') {
  window.createTickAnimation = createTickAnimation;
} 