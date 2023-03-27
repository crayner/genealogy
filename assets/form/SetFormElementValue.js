'use strict';

import FormValidation from './FormValidation'

export default function SetFormElementValue(event,element,type) {
    switch (type) {
        case 'time':
            element = setTimeValue(event,element)
            break
        case 'text':
            element = setStringValue(event,element)
            break
        case 'choice':
            element = setChoiceValue(event,element)
            break
        default:
            console.error('How do I handle the type === ' + type)
            console.log(event,element)
    }
    element = FormValidation(element)
    return element

    if (element.block_prefixes.includes('hillrange_toggle')) {
        element.value = element.value === '1' ? '0' : '1'
        element.data = element.value === '1'
    }
    element = FormValidation(element)
}

function setTimeValue(event, element){
    let section = event.target.getAttribute('time-type')
    let value = event.target.value

    if (typeof element.value !== 'object' || element.value === null)
        element.value = {hour: '0', minute: '0', second: '0'}

    if (typeof element.data !== 'object' || element.data === null)
        element.data = {date: '1970-01-01 00:00:00.000000', timezone_type: 3, timezone: 'UTC'}

    switch (section) {
        case 'hour':
            element.value.hour = value
            element.data.date = element.data.date.substr(0, 11) + value.padStart(2, '0') + element.data.date.substr(-13)
            element.children[0].value = value
            element.children[0].data = value
            if (element.with_minutes && typeof element.children[1].value === 'object') {
                element.children[1].value = '0'
                element.children[1].data = '0'
                element.value.minute = '0'
            }
            if (element.with_seconds && typeof element.children[2].value === 'object') {
                element.children[2].value = '0'
                element.children[2].data = '0'
                element.value.second = '0'
            }
            break
        case 'minute':
            element.value.minute = value
            element.data.date = element.data.date.substr(0, 14) + value.padStart(2, '0') + element.data.date.substr(-10)
            element.children[1].value = value
            element.children[1].data = value
            if (typeof element.children[0].value === 'object') {
                element.children[0].value = '0'
                element.children[0].date = '0'
                element.value.hour = '0'
            }
            if (element.with_seconds && typeof element.children[2].value === 'object') {
                element.children[2].value = '0'
                element.children[2].data = '0'
                element.value.second = '0'
            }
            break
        case 'second':
            element.value.second = value
            element.data.date = element.data.date.substr(0, 17) + value.padStart(2, '0') + element.data.date.substr(-7)
            element.children[2].value = value
            element.children[2].data = value
            if (typeof element.children[0].value === 'object') {
                element.children[0].value = '0'
                element.children[0].data = '0'
                element.value.hour = '0'
            }
            if (typeof element.children[1].value === 'object') {
                element.children[1].value = '0'
                element.children[1].data = '0'
                element.value.minute = '0'
            }
            break
        default:
            console.error('Handling time is difficult! ' + section)
    }
    return element
}

function setStringValue(event,element){
    const value = event.target.value
    element.value = value
    element.data = value
    return element
}

function setChoiceValue(event,element){
    if (element.multiple === true) {
        let value = element.value
        const newValue = event.target.value
        if (value.includes(newValue)) {
            const index = value.indexOf(newValue)
            value.splice(index, 1)
        } else {
            value.push(newValue)
        }
        element.value = value
        return element
    }

    const value = event.target.value
    element.value = value
    element.data = value
    return element
}
