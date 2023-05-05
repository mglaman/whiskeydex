import { faker } from '@faker-js/faker';

const email = faker.internet.email()
const password = faker.internet.password();
describe('user can login', () => {
  before(() => {
    cy.registerUser(email, password)
  })
  it('allows logging in', () => {
    cy.visit('http://127.0.0.1:8080')
    cy.get('a').contains('Log in').click();
    cy.get('input[name="name"]').type(email);
    cy.get('input[name="pass"]').type(password);
    cy.get('input[type="submit"]').contains('Log in').click();
    cy.contains('View profile')
    cy.get('.block-local-tasks-block').get('a').contains('Edit').click()
    cy.contains('Required if you want to change the Email address or Password below.');
  })
})
