'use strict';
import React from "react"

export function initialiseSections(template) {
    let result = {};
    Object.keys(template).map(i => {
        result[i] = false;
    })
    return result;
}
