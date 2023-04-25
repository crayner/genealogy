'use strict';
import React from 'react';
import * as ReactDOMClient from 'react-dom/client';
import IndividualApp from "./individual/IndividualApp";

const container = document.getElementById('individual-content');

// Create a root.
const root = ReactDOMClient.createRoot(container);

if (window.INDIVIDUAL_PROPS.addition) {
    root.render(<IndividualApp
        {...window.INDIVIDUAL_PROPS}
    />);
} else {
// Initial render: Render an element to the root.
    root.render(<IndividualApp
        {...window.INDIVIDUAL_PROPS}
    />);
}