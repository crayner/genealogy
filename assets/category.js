'use strict';
import React from 'react';
import * as ReactDOMClient from 'react-dom/client';
import CategoryApp from './category/CategoryApp';
import CategoryAddApp from "./category/CategoryAddApp";

const container = document.getElementById('category-content');

// Create a root.
const root = ReactDOMClient.createRoot(container);

if (window.CATEGORY_PROPS.addition) {
    root.render(<CategoryAddApp
        {...window.CATEGORY_PROPS}
    />);
} else {
// Initial render: Render an element to the root.
    root.render(<CategoryApp
        {...window.CATEGORY_PROPS}
    />);
}