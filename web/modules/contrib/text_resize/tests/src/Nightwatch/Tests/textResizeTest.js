module.exports = {
  '@tags': ['text_resize'],
  before(browser) {
    browser.drupalInstall({
      setupFile: `${__dirname}/../../TestSite/TestSiteTextResizeInstallTestScript.php`,
    });
  },
  beforeEach(browser) {
    browser
      .execute(function resetLocalStorage() {
        localStorage.clear();
      })
      .drupalRelativeURL('/')
      .waitForElementVisible('body');
  },
  after(browser) {
    browser.drupalUninstall();
  },
  'Text size can be increased': (browser) => {
    browser
      .click('#text_resize_increase')
      .assert.cssProperty('main', 'font-size', '19.2px')
      .click('#text_resize_increase')
      .click('#text_resize_increase')
      .assert.cssProperty(
        'main',
        'font-size',
        '25px',
        'Text size does not exceed maximum.',
      );
  },
  'Text size can be decreased': (browser) => {
    browser
      .click('#text_resize_decrease')
      .expect.element('main')
      .to.have.css('font-size')
      .which.startWith('13.3');

    browser
      .click('#text_resize_decrease')
      .assert.cssProperty(
        'main',
        'font-size',
        '12px',
        'Text size does not decrease below minimum.',
      );
  },
  'Text size is preserved on new page load': (browser) => {
    browser
      .click('#text_resize_increase')
      .refresh()
      .waitForElementVisible('body')
      .assert.cssProperty('main', 'font-size', '19.2px');
  },
  Resetting: (browser) => {
    browser
      .drupalLoginAsAdmin(() => {
        browser
          .drupalRelativeURL('/admin/config/user-interface/text_resize')
          .waitForElementVisible('body')
          .click('[data-drupal-selector="edit-text-resize-reset-button"]')
          .click('[data-drupal-selector="edit-submit"]');
      })
      .assert.elementPresent('#text_resize_reset')
      .click('#text_resize_increase')
      .click('#text_resize_reset')
      .assert.not.cssProperty('main', 'font-size', '19.2px');
  },
  'Line height can be changed with text size': (browser) => {
    browser
      .drupalLoginAsAdmin(() => {
        browser
          .drupalRelativeURL('/admin/config/user-interface/text_resize')
          .click('[data-drupal-selector="edit-text-resize-line-height-allow"]')
          .click('[data-drupal-selector="edit-submit"]');
      })
      // @todo Support parsing non-pixel values for `line-height`.
      .execute(function setNumericalLineHeight() {
        document.querySelector('main').style.lineHeight = '24px';
      })
      .click('#text_resize_increase')
      .expect.element('main')
      .to.have.css('line-height')
      .which.startWith('28.8');

    browser
      .click('#text_resize_increase')
      .click('#text_resize_increase')
      .assert.cssProperty(
        'main',
        'line-height',
        '36px',
        'Line height does not exceed maximum.',
      )
      .click('#text_resize_reset')
      // @todo Support parsing non-pixel values for `line-height`.
      .execute(function setNumericalLineHeight() {
        document.querySelector('main').style.lineHeight = '24px';
      })
      .click('#text_resize_decrease')
      .assert.cssProperty('main', 'line-height', '20px')
      .click('#text_resize_decrease')
      .assert.cssProperty(
        'main',
        'line-height',
        '16px',
        'Line height does not decrease below minimum.',
      )
      .click('#text_resize_decrease')
      .click('#text_resize_decrease')
      .assert.cssProperty(
        'main',
        'line-height',
        '16px',
        'Line height stays at minimum even when text size changes.',
      );
  },
  'Test scope option': (browser) => {
    const scopeInput = '[data-drupal-selector="edit-text-resize-scope"]';
    browser
      .drupalLoginAsAdmin(() => {
        browser
          .drupalRelativeURL('/admin/config/user-interface/text_resize')
          .clearValue(scopeInput)
          .setValue(scopeInput, 'block-text-resize')
          .click('[data-drupal-selector="edit-submit"]');
      })
      .click('#text_resize_increase')
      .assert.cssProperty('#block-text-resize', 'font-size', '19.2px')
      .assert.not.cssProperty('main', 'font-size', '19.2px')
      .execute(function resetLocalStorage() {
        localStorage.clear();
      })
      .drupalLoginAsAdmin(() => {
        browser
          .drupalRelativeURL('/admin/config/user-interface/text_resize')
          .clearValue(scopeInput)
          .setValue(scopeInput, 'dialog-off-canvas-main-canvas')
          .click('[data-drupal-selector="edit-submit"]');
      })
      .click('#text_resize_increase')
      .assert.cssProperty(
        '.dialog-off-canvas-main-canvas',
        'font-size',
        '19.2px',
      );
  },
};
