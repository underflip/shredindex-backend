oc.Modules.register('backend.component.inspector.dataloader', function () {
    class DataLoader {
        constructor() {
            this.cache = {};
        }

        requestOptions($el, serverClassName, handlerName, data, cacheKeyName, cacheKeyPropertyNames) {
            const cacheKeyPropertyValues = {};

            if (cacheKeyName && Array.isArray(cacheKeyPropertyNames)) {
                cacheKeyPropertyNames.forEach(prop => cacheKeyPropertyValues[prop] = data[prop]);
            }

            const key = !cacheKeyName ? null : JSON.stringify({
                serverClassName,
                cacheKeyName,
                cacheKeyPropertyValues
            });

            if (key !== null && key in this.cache) {
                if (typeof this.cache[key].then === 'function') {
                    return this.cache[key];
                }

                return Promise.resolve(this.cache[key]);
            }

            const promise = new Promise((resolve, reject) => {
                $($el).request(handlerName, {
                    data: data,
                    progressBar: false
                })
                    .done(data => {
                        key && (this.cache[key] = data);
                        resolve(data);
                    })
                    .fail(err => {
                        key && delete this.cache[key];
                        reject(err);
                    });
            });

            key && (this.cache[key] = promise);

            return promise;
        }
    }

    return new DataLoader();
});