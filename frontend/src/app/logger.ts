import { isObject, isFunction } from "util";

/**
 * @file: app/logger.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description: 
 * @todo:
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class Logger {
    //----------------------------------------------------------------------
    private name: string;
    private color: string;

    //----------------------------------------------------------------------

    public constructor(owner: any) {
        if (typeof owner == 'string') {
            this.name = owner;
        } else {
            this.name = owner.constructor.name;
        }

        this.color = this.getRandomColor();
    }
    //----------------------------------------------------------------------

    private parseValue(value : any) {

        if(isObject(value)) {
            value = JSON.stringify(value);
        }

        if(typeof value === "string") {
            value = '`' + value + '`';
        }

        if(isFunction(value)) {
            value = true;
        }

        return value;

    }
    //----------------------------------------------------------------------

    public getRandomColor() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }
    //----------------------------------------------------------------------

    public debug(source: string, output: string) {
        
        console.log("%c" +this.name + "%c::" + source + "()" + output, "color: " + this.color, "color: black");
    }
    //----------------------------------------------------------------------

    public startWith(source: string, params: any) : void {
        if(typeof params == "string") {
            console.log("%c" + this.name +  "%c::" + source + "() ->> string: `" + params + "`", "color: " + this.color, "color: black");

            return;
        }

        // build output string 
        if (Object.keys(params).length == 1) {
            for(let param in params) {
                console.log("%c" + this.name +  "%c::" + source + "() ->> " + param + ": `" + params[param] + "`", "color: " + this.color, "color: black");
            }

            return;
        }

        console.log("%c" + this.name +  "%c::" + source + "() ->>", "color: " + this.color, "color: black");
        
        for(let param in params) {
            console.log("%c"+ param + ": `" + params[param] + "`", "color: grey");
        }
    }
    //----------------------------------------------------------------------

    public recv(source:string, params: any, data: any) : void {
        if(typeof data === "string") {
            console.log("%c" + this.name +  "%c::" + source + "() ->> R> data: `" + data + "` ", "color: " + this.color, "color: black");
            

            return;
        }

        for(let param in params) {
            console.log("%c" + this.name +  "%c::" + source + "() ->> " + param + ": `" + params[param] + "` R>", "color: " + this.color, "color: black");
        }
        console.log(data);
    }
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------