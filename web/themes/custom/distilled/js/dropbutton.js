((Drupal) => {
  /**
   * Overrides the dropbutton toggle markup.
   *
   * We have to keep the 'dropbutton-toggle' CSS class because the dropbutton JS
   * operates with that one.
   *
   * @param {object} options
   *   Options object.
   * @param {string} [options.title]
   *   The button text.
   *
   * @return {string}
   *   A string representing a DOM fragment.
   */
  Drupal.theme.dropbuttonToggle = (options) =>
    `<li class="dropbutton-toggle">
<button class="p-2" type="button">
  <svg xmlns="http://www.w3.org/2000/svg" class="h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
  </svg>
  <span class="visually-hidden">${options.title}</span>
</button>
</li>`;
})(Drupal);
