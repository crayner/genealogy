'use strict';

import React, { Component } from "react"

export function cancelMessageByName(messages, name) {
    Object.keys(messages).map(key => {
        const message = messages[key]
        if (typeof message !== 'undefined')
            if (typeof message.name !== 'undefined')
                if (message.name === name)
                    messages.splice(key,1);
    })
    return messages;
}

export function setMessageByName(messages, name, error) {
    this.cancelMessageByName(messages, name);
    let message = {name: name, level: 'danger', message: error};
    messages.push(message);
    return messages;
}
