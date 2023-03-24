/**
 * @file
 * JavaScript file for the Text Resize module.
 */

{
  /**
   * Converts a value to an array.
   *
   * @template T
   *
   * @param {T} value
   *   The value to convert.
   *
   * @return {T[]}
   *   The value as an array.
   */
  const toArray = (value) => {
    if (!value) return [];
    return typeof value[Symbol.iterator] === 'function' ? [...value] : [value];
  };

  /**
   * @callback TextResizeMutator
   *
   * @param {{ fontSize?: number | string, lineHeight?: number | string }} sizes
   *   The sizes to apply.
   * @param {(value: number | string) => number} [parser]
   *   The parser function.
   *
   * @return {void}
   */

  /**
   * Creates a DOM mutator function.
   *
   * @param {HTMLElement[]} elements
   *   The set of elements to modify.
   * @param {boolean} [lineHeightAllow]
   *   Whether the line height can be modified.
   *
   * @return {TextResizeMutator}
   *   The DOM mutator function.
   */
  const createMutator = (elements, lineHeightAllow) => {
    const props = ['fontSize'];
    if (lineHeightAllow) props.push('lineHeight');

    return (sizes, parser = (value) => value) => {
      elements.forEach((element) => {
        props
          .filter((prop) => prop in sizes)
          .forEach((prop) => {
            element.style[prop] = sizes[prop] ? `${parser(sizes[prop])}px` : '';
          });
      });
    };
  };

  /** @type {WeakMap<HTMLAnchorElement,EventListener} */
  const listeners = new WeakMap();

  /**
   * @typedef TextResizeSettings
   *
   * @prop {string} text_resize_scope
   * @prop {number} text_resize_maximum
   * @prop {number} text_resize_minimum
   * @prop {bool} text_resize_line_height_allow
   * @prop {number} text_resize_line_height_min
   * @prop {number} text_resize_line_height_max
   */

  /**
   * @callback TextResizeModifier
   *
   * @param {{ fontSize: number, lineHeight: number }} sizes
   *   The size parameters to modify.
   * @param {TextResizeSettings} settings
   *   Text resize settings.
   *
   * @return {{ fontSize: number, lineHeight?: number }}
   *   The modified sizes.
   */

  /**
   * Creates a sizes modifier.
   *
   * @param {number} modifier
   *   The multiplicative modifier value.
   *
   * @return {TextResizeModifier}
   *   The modifier.
   */
  const createModifier = (modifier) => {
    const clampProp = modifier < 1 ? 'min' : 'max';
    const clamp = modifier < 1 ? Math.max : Math.min;

    return ({ fontSize, lineHeight }, settings) => {
      const fontSizeLimit = settings[`text_resize_${clampProp}imum`];
      const result = {
        fontSize: clamp(fontSize * modifier, fontSizeLimit),
      };

      if (settings.text_resize_line_height_allow) {
        result.lineHeight =
          result.fontSize === fontSizeLimit
            ? settings[`text_resize_line_height_${clampProp}`]
            : lineHeight * modifier;
      }

      localStorage.setItem('textResize', JSON.stringify(result));
      return result;
    };
  };

  const OPERATORS = {
    text_resize_increase: createModifier(1.2),
    text_resize_decrease: createModifier(1 / 1.2),
    text_resize_reset: (_, settings) => {
      const result = { fontSize: null };

      if (settings.text_resize_line_height_allow) {
        result.lineHeight = null;
      }

      localStorage.removeItem('textResize');
      return result;
    },
  };

  Drupal.behaviors.textResize = {
    attach(context, { text_resize: settings = {} }) {
      const {
        // Which div or page element are we resizing?
        text_resize_scope: scope,
        text_resize_line_height_allow: lineHeightAllow,
      } = settings;

      /** @type {HTMLElement[]} */
      let elementToResize = [];

      if (scope) {
        elementToResize = [
          'getElementById',
          'getElementsByClassName',
          'querySelectorAll',
        ].reduce(
          (accumulator, func) =>
            accumulator.length ? accumulator : toArray(document[func](scope)),
          elementToResize,
        );
      } else {
        elementToResize = [
          'page',
          'content-inner',
          '#squeeze > #content',
        ].reduce(
          (accumulator, id, i) =>
            accumulator.length
              ? accumulator
              : toArray(
                  document[i === 2 ? 'querySelectorAll' : 'getElementById'](id),
                ),
          elementToResize,
        );
      }

      if (elementToResize.length) {
        const mutate = createMutator(elementToResize, lineHeightAllow);

        // Set the initial font size if necessary.
        if (typeof localStorage.textResize !== 'undefined') {
          try {
            mutate(JSON.parse(localStorage.textResize), parseFloat);
          } catch (e) {
            // No-op.
          }
        }

        const onClick = (event) => {
          event.preventDefault();
          const { currentTarget: button } = event;
          const styles = getComputedStyle(elementToResize[0]);
          const sizes = OPERATORS[button.id](
            {
              // Set the current font size of the specified section as a variable.
              fontSize: parseFloat(styles.fontSize),
              // Set the current line-height.
              lineHeight: parseFloat(styles.lineHeight),
            },
            settings,
          );

          mutate(sizes);
        };

        once('text-resize', 'a.changer').forEach((button) => {
          listeners.set(button, onClick);
          button.addEventListener('click', onClick);
        });
      }
    },
    detach(context, _, trigger) {
      if (trigger === 'unload') {
        once
          .filter('text-resize', 'a.changer', context)
          .filter((button) => listeners.has(button))
          .forEach((button) =>
            button.removeEventListener('click', listeners.get(button)),
          );
      }
    },
  };
}
