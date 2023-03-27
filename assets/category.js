'use strict';
import React from 'react';
import * as ReactDOMClient from 'react-dom/client';
import CategoryApp from './category/CategoryApp';

const container = document.getElementById('category-content');

// Create a root.
const root = ReactDOMClient.createRoot(container);

// Initial render: Render an element to the root.
root.render(<CategoryApp
    {...window.CATEGORY_PROPS}
/>);
