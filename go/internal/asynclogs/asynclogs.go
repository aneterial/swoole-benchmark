package asynclogs

import "log"

func Info(format string, v ...any) {
	go func() {
		log.Printf(format, v...)
	}()
}
