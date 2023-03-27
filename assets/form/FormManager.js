'use strict';
import React from "react"
import FormValidation from "./FormValidation";
import {setMessageByName} from "./MessageManager";

let elementList = {};

export function getFormElementById(form, id, refresh = false) {
    if (refresh === true)
        elementList = {}
    if (typeof elementList[id] === 'undefined')
        elementList = buildElementList({}, form)
    return elementList[id]
}

function buildElementList(list, form) {
    list[form.id] = form
    form.children.map(child => {
        buildElementList(list, child)
    })
    return list
}

export function setFormElement(element, form) {
    if (element.id === form.id)
    {
        form = {...element}
        return form
    }
    form.children.map(child => {
        setFormElement(element, child)
    })
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
            setMessageByElementErrors(child)
        })
        return data
    } else {
        setMessageByElementErrors(form)
        return form.value
    }
}

function setMessageByElementErrors(element){
    element = FormValidation(element)
    element.errors.map(error => {
        setMessageByName(element.id, (element.label ? element.label : element.name ) + ' (' + (!(!element.value || /^\s*$/.test(element.value)) ? JSON.stringify(element.value) : '{empty}') + '): ' + error)
    })
}

export function isOKtoSave(messages) {
    if (messages.length === 0)
        return true
    let ok = true
    messages.map(message => {
        if (['warning','danger'].includes(message.level))
            ok = false
    })
    return ok
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






