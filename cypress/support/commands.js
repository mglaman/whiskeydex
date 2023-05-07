// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })

Cypress.Commands.add('registerUser', (email, password) => {
  cy.visit('http://127.0.0.1:8080')
  cy.get('a').contains('Create new account').click();
  cy.get('input[name="mail"]').type(email);
  cy.get('input[name="name"]').should('not.exist');
  cy.get('input[name="pass[pass1]"]').type(password);
  cy.get('input[name="pass[pass2]"]').type(password);
  cy.get('input[type="submit"]').contains('Create new account').click();
  cy.contains('Registration successful. You are now logged in.', {timeout: 1000});
  cy.visit('http://127.0.0.1:8080/account/logout')
});

Cypress.Commands.add('loginAsUser', (email, password) => {
  cy.visit('http://127.0.0.1:8080')
  cy.get('a').contains('Log in').click();
  cy.get('input[name="name"]').type(email);
  cy.get('input[name="pass"]').type(password);
  cy.get('input[type="submit"]').contains('Log in').click();
})
