#!/usr/bin/python
import os
import time
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler


class MyHandler(FileSystemEventHandler):
    def on_modified(self, event):
        if event.src_path.endswith("pause_file.json"):
            print("SOMETHING HAPPENED")


if __name__ == "__main__":
    event_handler = MyHandler()
    observer = Observer()
    observer.schedule(event_handler, path=os.path.join(os.path.dirname(__file__) + "/cache"), recursive=False)
    observer.start()

    #https://stackoverflow.com/questions/18599339/watchdog-monitoring-file-for-changes

    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        observer.stop()
    observer.join()
