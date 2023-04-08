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
    form = {...form};
    if (form.children.length > 0 && form.type !== 'collection') {
        form.children.map(child => {
            data[child.name] = buildFormData({}, child);
        })
        return data;
    } else if (form.children.length > 0 && form.allow_add === true) {
        form.children.map((child, i) => {
            data[i] = [];
            child.children.map((subForm, o) => {
                if (typeof subForm.value === 'object' && typeof subForm.value.id !== 'undefined') {
                    data[i][subForm.name] = subForm.value.id;
                } else {
                    data[i][subForm.name] = subForm.value;
                }
            });
        });
        return Object.keys(data).map(i => {
            return {...data[i]};
        });
    } else {
        if (typeof form.value === 'object' && form.value !== null && form.value.length > 1) {
            let result = form.value.map(value => {
                return value;
            })
            return result;
        }
        return form.value;
    }
}

export function extractFormSection(form, section) {
    let result = {...form};
    result.children = [];
    let  elements = form.template[section].elements;
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






