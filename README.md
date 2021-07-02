# DesignerCMS
This project was inspired by a picky client and evolved in later jobs where I became frustrated recreating database queries and data validation.

The intention is that the database structure can be defined in PHP classes and to auto generate these classes. With this improved integration it would be easier to manipulate data and this would create the foundation of a dynamic application. I am currently creating the building blocks which can later be pieced together to form any project imaginable.

The core pieces here are the DataType class and subclasses which provide a strong interface with existing MySQL types. I am introducing all MySQL DataTypes as PHP classes. For example, I am enabling BigInt which would enable the storage of very large numbers in both Signed and Unsigned formats. The logic for performing arithmetic on these new types is still in progress and I encourage feedback to improve the performance of this functionality.

In the later stages, I intend for this project to be a tool for anyone to create their own web applications with minimal programming knowledge. The entire programming and design process can be a visual experience where the client can be their own designer and programmer.
