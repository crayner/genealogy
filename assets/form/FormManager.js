'use strict';
import React from "react"
import FormValidation from "./FormValidation";

let elementList;

export function getFormElementById(form, id, refresh = false) {
    form = {...form};
    if (refresh === true)
        elementList = {}
    if (typeof elementList[id] === 'undefined' || elementList === {})
        elementList = buildElementList({}, form);
    return elementList[id]
}

function buildElementList(list, form) {
    form = {...form};
    list[form.id] = form;
    form.children.map(child => {
        buildElementList(list, child);
    })
    return list
}

export function setFormElement(element, form) {
    form = {...form};
    if (element.id === form.id)
    {
        return {...element};
    }
    form.children.map((child, i) => {
        child = setFormElement(element, {...child});
        form.children[i] = {...child};
    })
    return form;
}

export function followUrl(details,element) {
    const url = details.url
    const options = (details.url_options && typeof details.url_options === 'object') ? details.url_options : {}
    const type = (details.url_type && typeof details.url_type === 'string') ? details.url_type : 'redirect'
    this.handleURLCall(url,options,type,element)
}

export function buildFormData(data, form) {
    if (form.children.length > 0) {
        form.children.map(child => {
            data[child.name] = buildFormData({}, child)
        })
        return data
    } else {
        return form.value
    }
}

export function extractFormSection(form, section) {
    var result = {...form};
    result.children = [];
    var elements = form.template[section].elements;
    result.children = form.children.filter((child, i) => {
        if (child.name === 'id') {
            return child;
        }
        if (elements.includes(child.name)) {
            return child;
        }
        if (child.name === 'doit') {
            return child;
        }
    })
    return result;
}






