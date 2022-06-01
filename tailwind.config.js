module.exports = {
  content: [
    './web/themes/custom/distilled/templates/**/*.twig',
    './web/themes/custom/distilled/js/**/*.js',
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
