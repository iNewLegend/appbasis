/**
 * @file: app/validator.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo: you can avoid this file
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class Validator {
    
    static isEmail(email: string): boolean {
        let EMAIL_REGEXP = /^[a-z0-9!#$%&'*+\/=?^_`{|}~.-]+@[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)*$/i;

        if(email.length <= 0) {
            return false;
        }

        if (email != "" && (email.length <= 5 || !EMAIL_REGEXP.test(email))) {
            return false;
        }

        return true;
    }
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------