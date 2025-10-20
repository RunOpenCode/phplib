# Configuration file for the Sphinx documentation builder.
#
# For the full list of built-in configuration values, see the documentation:
# https://www.sphinx-doc.org/en/master/usage/configuration.html

# -- Project information -----------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#project-information

project = 'PHP Libraries'
copyright = '2025, RunOpenCode'
author = 'Nikola Svitlica a.k.a TheCelavi'

# -- General configuration ---------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#general-configuration

extensions = [
    'sphinx_favicon',
    'sphinxcontrib.phpdomain',
    'sphinx_copybutton',
    'sphinx_inline_tabs',
]

templates_path = ['_templates']
exclude_patterns = []

# -- Options for HTML output -------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#options-for-html-output

html_theme = 'furo'
html_static_path = ['_static']
html_theme_path = ['.']
html_theme_options = {
    'light_logo': 'logo/run-open-code-black.svg',
    'dark_logo': 'logo/run-open-code-white.svg',
}
html_css_files = [
    'css/layout.css',
]
html_title = 'PHP Libraries'
# Favicon configuration
favicons = [
    {
        'href': 'logo/run-open-code-black.svg'
    },
]
